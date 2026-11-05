<?php

namespace Tests\Feature\Api;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BukuApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Kategori $kategoriInf;
    private Kategori $kategoriMat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Pustakawan Uji',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        $this->kategoriInf = Kategori::create(['kode_kategori' => 'INF', 'nama_kategori' => 'Informatika']);
        $this->kategoriMat = Kategori::create(['kode_kategori' => 'MAT', 'nama_kategori' => 'Matematika']);
    }

    public function test_it_returns_paginated_books_with_filters_and_hateoas(): void
    {
        Buku::create([
            'isbn' => '9780000000001',
            'judul' => 'Algoritma dan Struktur Data',
            'penulis' => 'Rinaldi Munir',
            'penerbit' => 'Informatika Press',
            'tahun_terbit' => 2021,
            'kategori_id' => $this->kategoriInf->id,
            'stok' => 5,
            'sinopsis' => 'Buku algoritma lengkap.',
        ]);

        Buku::create([
            'isbn' => '9780000000002',
            'judul' => 'Kalkulus Lanjut',
            'penulis' => 'Purcell',
            'penerbit' => 'Erlangga',
            'tahun_terbit' => 2020,
            'kategori_id' => $this->kategoriMat->id,
            'stok' => 0,
            'sinopsis' => 'Buku kalkulus stok habis.',
        ]);

        // 1. Uji GET publik tanpa filter
        $response = $this->getJson('/api/v1/bukus?per_page=10');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'isbn', 'judul', 'stok', '_links'],
            ],
            'meta' => ['current_page', 'per_page', 'total_items', 'total_pages', 'links'],
        ]);

        $data = $response->json('data');
        $bukuStokTersedia = collect($data)->firstWhere('isbn', '9780000000001');
        $bukuStokHabis = collect($data)->firstWhere('isbn', '9780000000002');

        // HATEOAS: Link pinjam_buku HARUS ADA jika stok > 0
        $this->assertArrayHasKey('pinjam_buku', $bukuStokTersedia['_links']);
        // HATEOAS: Link pinjam_buku TIDAK BOLEH ADA jika stok === 0
        $this->assertArrayNotHasKey('pinjam_buku', $bukuStokHabis['_links']);

        // 2. Uji Filter ketersediaan ?tersedia=1
        $resFilter = $this->getJson('/api/v1/bukus?tersedia=1');
        $resFilter->assertStatus(200);
        $this->assertCount(1, $resFilter->json('data'));
        $this->assertEquals('9780000000001', $resFilter->json('data.0.isbn'));

        // 3. Uji Search ?search=Kalkulus
        $resSearch = $this->getJson('/api/v1/bukus?search=Kalkulus');
        $resSearch->assertStatus(200);
        $this->assertCount(1, $resSearch->json('data'));
        $this->assertEquals('Kalkulus Lanjut', $resSearch->json('data.0.judul'));
    }

    public function test_it_returns_single_book_detail_or_rfc_7807_when_not_found(): void
    {
        $buku = Buku::create([
            'isbn' => '9786020332900',
            'judul' => 'Sistem Basis Data ACID',
            'penulis' => 'Chandro Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategoriInf->id,
            'stok' => 3,
        ]);

        // Cari by ID
        $resId = $this->getJson("/api/v1/bukus/{$buku->id}");
        $resId->assertStatus(200);
        $resId->assertJsonPath('data.judul', 'Sistem Basis Data ACID');

        // Cari by ISBN
        $resIsbn = $this->getJson("/api/v1/bukus/9786020332900");
        $resIsbn->assertStatus(200);
        $resIsbn->assertJsonPath('data.isbn', '9786020332900');

        // Tidak Ditemukan -> 404 Problem Details
        $res404 = $this->getJson("/api/v1/bukus/99999");
        $res404->assertStatus(404);
        $res404->assertHeader('Content-Type', 'application/problem+json');
        $res404->assertJsonPath('title', 'Resource Not Found');
    }

    public function test_it_creates_new_book_with_201_and_location_header(): void
    {
        $payload = [
            'isbn' => '9786020332909',
            'judul' => 'Arsitektur Web Services Terdistribusi',
            'penulis' => 'Chandro Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $this->kategoriInf->id,
            'stok' => 10,
            'sinopsis' => 'Buku praktikum web services IT Del.',
        ];

        // Tanpa token -> 401
        $resUnauth = $this->postJson('/api/v1/bukus', $payload);
        $resUnauth->assertStatus(401);

        // Dengan token -> 201 Created + Header Location
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/bukus', $payload);

        $response->assertStatus(201);
        $newId = $response->json('data.id');
        $this->assertNotNull($newId);
        $response->assertHeader('Location', url("/api/v1/bukus/{$newId}"));
        $this->assertDatabaseHas('bukus', ['isbn' => '9786020332909', 'stok' => 10]);
    }

    public function test_it_demonstrates_semantic_differences_between_put_and_patch(): void
    {
        $buku = Buku::create([
            'isbn' => '9781111222233',
            'judul' => 'Dasar Jaringan Komputer',
            'penulis' => 'Andrew S. Tanenbaum',
            'penerbit' => 'Pearson',
            'tahun_terbit' => 2019,
            'kategori_id' => $this->kategoriInf->id,
            'stok' => 4,
            'sinopsis' => 'Buku jaringan komputer klasik.',
        ]);

        // 1. SEMANTIK PATCH: Cukup mengirim stok saja, judul & sinopsis tetap aman
        $patchRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/bukus/{$buku->id}", [
                'stok' => 8,
            ]);

        $patchRes->assertStatus(200);
        $buku->refresh();
        $this->assertEquals(8, $buku->stok);
        $this->assertEquals('Buku jaringan komputer klasik.', $buku->sinopsis);

        // 2. SEMANTIK PUT: Wajib seluruh field. Jika sinopsis tidak dikirim, maka direset ke null!
        $putPayload = [
            'isbn' => '9781111222233',
            'judul' => 'Dasar Jaringan Komputer Edisi 6',
            'penulis' => 'Andrew S. Tanenbaum',
            'penerbit' => 'Pearson Updated',
            'tahun_terbit' => 2022,
            'kategori_id' => $this->kategoriInf->id,
            'stok' => 12,
            // sinopsis sengaja tidak disertakan
        ];

        $putRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/bukus/{$buku->id}", $putPayload);

        $putRes->assertStatus(200);
        $buku->refresh();
        $this->assertEquals(12, $buku->stok);
        $this->assertEquals('Dasar Jaringan Komputer Edisi 6', $buku->judul);
        $this->assertNull($buku->sinopsis);
    }

    public function test_it_prevents_deletion_with_409_conflict_when_active_loan_exists(): void
    {
        $buku = Buku::create([
            'isbn' => '9789999888877',
            'judul' => 'Struktur Data C++',
            'penulis' => 'Adam Drozdek',
            'penerbit' => 'Course Technology',
            'tahun_terbit' => 2018,
            'kategori_id' => $this->kategoriInf->id,
            'stok' => 2,
        ]);

        // Buat peminjaman berstatus 'dipinjam'
        Peminjaman::create([
            'kode_pinjam' => 'PINJ-CONFLICT-001',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S22010',
            'nama_peminjam' => 'Siti Siregar',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);

        // Coba hapus buku -> Harus ditolak 409 Conflict
        $delConflict = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/bukus/{$buku->id}");

        $delConflict->assertStatus(409);
        $delConflict->assertHeader('Content-Type', 'application/problem+json');
        $this->assertDatabaseHas('bukus', ['id' => $buku->id]);

        // Ubah status peminjaman menjadi 'dikembalikan'
        Peminjaman::where('kode_pinjam', 'PINJ-CONFLICT-001')->update(['status' => 'dikembalikan']);

        // Sekarang hapus kembali -> Harus sukses 204 No Content
        $delSuccess = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/bukus/{$buku->id}");

        $delSuccess->assertStatus(204);
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }
}
