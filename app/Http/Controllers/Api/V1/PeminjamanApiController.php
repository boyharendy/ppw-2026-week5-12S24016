<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans
     * Menampilkan daftar transaksi sirkulasi peminjaman buku (Filter status, NIM, buku_id)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Peminjaman::query()
            ->with(['buku.kategori'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('nim'), fn ($q) => $q->where('nim_peminjam', $request->query('nim')))
            ->when($request->filled('buku_id'), fn ($q) => $q->where('buku_id', $request->query('buku_id')))
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar transaksi sirkulasi peminjaman berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/peminjamans/{id}
     * Detail satu transaksi peminjaman
     */
    public function show(string $id): JsonResponse
    {
        $pinjam = Peminjaman::with(['buku.kategori'])->find($id);

        if (!$pinjam) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'peminjaman-not-found'
            );
        }

        return $this->successResponse(
            data: new PeminjamanResource($pinjam),
            message: 'Detail peminjaman buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/peminjamans
     * Mencatat peminjaman buku baru dengan Transaksi ACID + lockForUpdate (Pessimistic Locking)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|integer|exists:bukus,id',
            'nim_peminjam' => 'required|string|max:15',
            'nama_peminjam' => 'required|string|max:150',
            'durasi_hari' => 'nullable|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated) {
            $nim = trim($validated['nim_peminjam']);
            $bukuId = $validated['buku_id'];

            // 1. Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE) untuk mencegah race condition
            $buku = Buku::where('id', $bukuId)->lockForUpdate()->first();

            if (!$buku) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: 'Buku yang dipilih tidak ditemukan.',
                    status: 404,
                    errorSlug: 'buku-not-found'
                );
            }

            // 2. Cek apakah mahasiswa masih meminjam buku yang sama dan belum mengembalikannya -> 409 Conflict
            $activeDuplicate = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $nim)
                ->where('status', 'dipinjam')
                ->exists();

            if ($activeDuplicate) {
                return $this->problemResponse(
                    title: 'Duplicate Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$nim}' sedang meminjam buku ini dan belum mengembalikannya.",
                    status: 409,
                    errorSlug: 'duplicate-loan'
                );
            }

            // 3. Cek ketersediaan stok buku -> 422 Unprocessable Content jika stok habis
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Book Out of Stock',
                    detail: "Maaf, stok buku '{$buku->judul}' sedang habis (0 tersisa).",
                    status: 422,
                    errorSlug: 'stok-habis'
                );
            }

            // 4. Kurangi stok buku (-1) secara atomik
            $buku->decrement('stok');

            // 5. Buat kode pinjam unik dan hitung tanggal
            $durasiHari = (int) ($validated['durasi_hari'] ?? 7);
            $kodePinjam = 'PINJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $tglPinjam = now()->toDateString();
            $tglJatuhTempo = now()->addDays($durasiHari)->toDateString();

            $peminjaman = Peminjaman::create([
                'kode_pinjam' => $kodePinjam,
                'buku_id' => $buku->id,
                'nim_peminjam' => $nim,
                'nama_peminjam' => $validated['nama_peminjam'],
                'tanggal_pinjam' => $tglPinjam,
                'tanggal_jatuh_tempo' => $tglJatuhTempo,
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
            ]);

            $peminjaman->load('buku.kategori');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok kini: {$buku->fresh()->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan
     * Mengembalikan buku dalam transaksi ACID (lockForUpdate) dan memulihkan stok (+1)
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::find($id);

            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'peminjaman-not-found'
                );
            }

            // Tolak pengembalian ganda jika status sudah 'dikembalikan' -> 409 Conflict
            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict — Book Already Returned',
                    detail: 'Buku pada transaksi ini sudah pernah dikembalikan sebelumnya.',
                    status: 409,
                    errorSlug: 'already-returned'
                );
            }

            // Kunci baris buku dan pulihkan stok (+1) secara atomik
            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            if ($buku) {
                $buku->increment('stok');
            }

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            $peminjaman->load('buku.kategori');

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman),
                message: "Buku '{$peminjaman->buku?->judul}' berhasil dikembalikan dan stok telah dipulihkan (+1)."
            );
        });
    }
}
