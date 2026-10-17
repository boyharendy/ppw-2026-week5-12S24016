<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class PeminjamanController extends Controller
{
    /**
     * 1. Menampilkan daftar semua transaksi peminjaman
     */
    public function index(Request $request)
    {
        $query = Peminjaman::with('buku');

        // Pencarian berdasarkan NIM, Nama Mahasiswa, atau Kode Pinjam
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nim_peminjam', 'like', "%{$search}%")
                  ->orWhere('nama_peminjam', 'like', "%{$search}%")
                  ->orWhere('kode_pinjam', 'like', "%{$search}%");
            });
        }

        // Filter status (dipinjam / dikembalikan)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjamans = $query->latest()->paginate(10)->withQueryString();

        return view('peminjaman.index', compact('peminjamans'));
    }

    /**
     * 2. Menampilkan Form Peminjaman Buku
     */
    public function create(Request $request)
    {
        // Hanya tampilkan buku yang stoknya masih > 0
        $bukus = Buku::where('stok', '>', 0)->orderBy('judul')->get();
        $selectedBukuId = $request->buku_id;

        return view('peminjaman.create', compact('bukus', 'selectedBukuId'));
    }

    /**
     * 3. TRANSAKSI PEMINJAMAN (ACID & PESSIMISTIC LOCKING)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'buku_id'             => 'required|exists:bukus,id',
            'nim_peminjam'        => 'required|string|min:6|max:20',
            'nama_peminjam'       => 'required|string|min:3|max:150',
            'tanggal_pinjam'      => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_pinjam',
        ]);

        try {
            // Membungkus seluruh proses dalam DB::transaction (retry 3x jika deadlock)
            DB::transaction(function () use ($validated) {
                // A. Kunci baris buku dengan lockForUpdate() agar stok tidak balapan (Race Condition)
                $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->firstOrFail();

                // B. Periksa stok buku (Rollback & tolak jika stok = 0)
                if ($buku->stok < 1) {
                    throw new \RuntimeException("Stok buku '{$buku->judul}' telah habis!");
                }

                // C. Mencegah NIM yang sama meminjam buku yang sama jika belum dikembalikan
                $sedangDipinjam = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $validated['nim_peminjam'])
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($sedangDipinjam) {
                    throw new \RuntimeException("Mahasiswa dengan NIM {$validated['nim_peminjam']} masih meminjam buku ini dan belum mengembalikannya!");
                }

                // D. Kurangi stok buku secara atomik
                $buku->decrement('stok');

                // E. Buat kode transaksi unik, misal: PINJ-20261013-X7Y9Z
                $kodePinjam = 'PINJ-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // F. Simpan rekaman peminjaman
                Peminjaman::create([
                    'kode_pinjam'         => $kodePinjam,
                    'buku_id'             => $buku->id,
                    'nim_peminjam'        => strtoupper(trim($validated['nim_peminjam'])),
                    'nama_peminjam'       => $validated['nama_peminjam'],
                    'tanggal_pinjam'      => $validated['tanggal_pinjam'],
                    'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                    'status'              => 'dipinjam',
                ]);
            }, 3);

            return redirect()->route('peminjaman.index')->with('success', 'Transaksi peminjaman buku berhasil dicatat secara atomik!');
        } catch (\RuntimeException $e) {
            // Tangkap penolakan aturan bisnis (stok habis / pinjam ganda)
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            // Tangkap galat level database dan catat ke Log
            Log::error('[DB EXCEPTION PEMINJAMAN]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem basis data saat memproses transaksi.');
        }
    }

    /**
     * 4. TRANSAKSI PENGEMBALIAN BUKU (ACID & PESSIMISTIC LOCKING)
     */
    public function kembalikan(Peminjaman $peminjaman)
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                // Kunci baris buku yang bersangkutan
                $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->firstOrFail();

                // Pastikan status memang masih 'dipinjam'
                if ($peminjaman->status === 'dikembalikan') {
                    throw new \RuntimeException("Buku ini sudah dikembalikan sebelumnya!");
                }

                // A. Ubah status menjadi 'dikembalikan' dan catat tanggal hari ini
                $peminjaman->update([
                    'status'          => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // B. Kembalikan stok buku (+1)
                $buku->increment('stok');
            }, 3);

            return back()->with('success', 'Buku berhasil dikembalikan dan stok buku telah diperbarui!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION PENGEMBALIAN]: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pengembalian buku di basis data.');
        }
    }
}
