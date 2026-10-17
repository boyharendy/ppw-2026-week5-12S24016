<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pustakawan Uji (Sesuai Modul Praktikum Minggu 6)
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name'              => 'Pustakawan Del',
                'password'          => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Kategori Koleksi Baku Perpustakaan
        $kategoris = [
            ['kode_kategori' => 'INF', 'nama_kategori' => 'Informatika'],
            ['kode_kategori' => 'ELK', 'nama_kategori' => 'Elektro'],
            ['kode_kategori' => 'BIO', 'nama_kategori' => 'Biologi'],
            ['kode_kategori' => 'MDK', 'nama_kategori' => 'Manajemen dan Bisnis'],
            ['kode_kategori' => 'MAT', 'nama_kategori' => 'Matematika Dasar'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::firstOrCreate(['kode_kategori' => $kategori['kode_kategori']], $kategori);
        }

        // 3. Sampel Buku Khusus Pengujian Transaksi ACID:
        // A. Buku dengan Stok = 1 (Uji Concurrency & Stok Kritis)
        $bukuStokSatu = Buku::updateOrCreate(
            ['isbn' => '9786020332900'],
            [
                'judul'        => 'Sistem Basis Data ACID dan Konkurensi',
                'penulis'      => 'Chandro Pardede',
                'penerbit'     => 'Del Press',
                'tahun_terbit' => 2024,
                'kategori_id'  => 1,
                'stok'         => 1,
                'sinopsis'     => 'Buku pegangan uji transaksi atomik dan pessimistic locking pada basis data relasional.',
            ]
        );

        // B. Buku dengan Stok = 0 (Uji Penolakan Peminjaman & Rollback Transaksi)
        Buku::updateOrCreate(
            ['isbn' => '9786020332901'],
            [
                'judul'        => 'Arsitektur Web Modern dan Sesi Server',
                'penulis'      => 'Boy Harendy',
                'penerbit'     => 'Del Press',
                'tahun_terbit' => 2025,
                'kategori_id'  => 1,
                'stok'         => 0,
                'sinopsis'     => 'Buku sampel dengan stok habis untuk menguji penolakan transaksi dan rollback atomik.',
            ]
        );

        // C. Koleksi Buku Lainnya
        Buku::factory(16)->create();

        // 4. Sampel Transaksi Peminjaman (Dipinjam & Dikembalikan)
        // A. Transaksi Aktif (Sedang Dipinjam)
        $bukuPinjam = Buku::where('stok', '>', 1)->first();
        if ($bukuPinjam) {
            Peminjaman::create([
                'kode_pinjam'         => 'PINJ-20261015-X1Y2Z',
                'buku_id'             => $bukuPinjam->id,
                'nim_peminjam'        => '12S24016',
                'nama_peminjam'       => 'Boy Harendy',
                'tanggal_pinjam'      => now()->subDays(3)->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
                'status'              => 'dipinjam',
            ]);
            $bukuPinjam->decrement('stok');
        }

        // B. Transaksi Selesai (Sudah Dikembalikan)
        $bukuKembali = Buku::where('stok', '>', 0)->skip(1)->first();
        if ($bukuKembali) {
            Peminjaman::create([
                'kode_pinjam'         => 'PINJ-20261010-A9B8C',
                'buku_id'             => $bukuKembali->id,
                'nim_peminjam'        => '12S24001',
                'nama_peminjam'       => 'Mahasiswa Del',
                'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
                'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
                'tanggal_kembali'     => now()->subDays(2)->toDateString(),
                'status'              => 'dikembalikan',
            ]);
        }
    }
}
