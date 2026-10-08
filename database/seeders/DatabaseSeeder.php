<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $kategoris = [
            ['kode_kategori' => 'INF', 'nama_kategori' => 'Informatika'],
            ['kode_kategori' => 'ELK', 'nama_kategori' => 'Elektro'],
            ['kode_kategori' => 'BIO', 'nama_kategori' => 'Biologi'],
            ['kode_kategori' => 'MDK', 'nama_kategori' => 'Manajemen dan Bisnis'],
            ['kode_kategori' => 'MAT', 'nama_kategori' => 'Matematika Dasar'],
        ];

        foreach ($kategoris as $kategori) {
            \App\Models\Kategori::create($kategori);
        }

        \App\Models\Buku::factory(18)->create();
        \App\Models\Buku::factory(2)->create(['stok' => 0]);
    }
}
