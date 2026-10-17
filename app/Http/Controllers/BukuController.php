<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class BukuController extends Controller
{
    public function index(Request $request)
    {
        // 1. Simpan state sesi: Kata kunci pencarian terakhir
        if ($request->filled('search')) {
            session(['last_search' => $request->search]);
        }
        // 2. Query katalog dengan withCount('peminjamans') dan filter dinamis when()
        $bukus = Buku::with('kategori')
            ->withCount('peminjamans') // Menghitung total peminjaman secara efisien
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->kategori_id, function ($q, $kategoriId) {
                $q->where('kategori_id', $kategoriId);
            })
            ->orderBy('judul')
            ->paginate(10)
            ->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        // 3. Rekapitulasi Statistik Perpustakaan menggunakan Fluent Query Builder (DB::table)
        $stats = [
            'total_judul'    => DB::table('bukus')->count(),
            'total_stok'     => (int) DB::table('bukus')->sum('stok'),
            'total_dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_kembali'  => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];
        return view('buku.index', compact('bukus', 'kategoris', 'stats'));
    }


    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn' => 'required|string|size:13|unique:bukus',
            'judul' => 'required|string|min:5|max:200',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:100',
            'tahun_terbit' => 'required|integer|min:1900|max:' . date('Y'),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string|max:2000',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(Buku $buku)
    {
        $buku->load('kategori');
        return view('buku.show', compact('buku'));
    }

    public function edit(Buku $buku)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.edit', compact('buku', 'kategoris'));
    }

    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'isbn' => 'required|string|size:13|unique:bukus,isbn,' . $buku->id,
            'judul' => 'required|string|min:5|max:200',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:100',
            'tahun_terbit' => 'required|integer|min:1900|max:' . date('Y'),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string|max:2000',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus.');
    }
}
