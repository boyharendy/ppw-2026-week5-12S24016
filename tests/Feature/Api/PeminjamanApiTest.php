<?php

namespace Tests\Feature\Api;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PeminjamanApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Kategori $kategori;
    private Buku $bukuStokSatu;
    private Buku $bukuStokHabis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Pustakawan Uji',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        $this->kategori = Kategori::create(['kode_kategori' => 'INF', 'nama_kategori' => 'Informatika']);

        $this->bukuStokSatu = Buku::create([
            'isbn' => '9786020332901',
            'judul' => 'Sistem Basis Data ACID',
            'penulis' => 'Chandro Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategori->id,
            'stok' => 1,
        ]);

        $this->bukuStokHabis = Buku::create([
            'isbn' => '9786020332902',
            'judul' => 'Buku Habis',
            'penulis' => 'Anonim',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2023,
            'kategori_id' => $this->kategori->id,
            'stok' => 0,
        ]);
    }

    public function test_it_records_new_borrowing_with_acid_and_decrements_stock(): void
    {
        $payload = [
            'buku_id' => $this->bukuStokSatu->id,
            'nim_peminjam' => '12S22001',
            'nama_peminjam' => 'Bona Pandiangan',
            'durasi_hari' => 7,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', $payload);

        $response->assertStatus(201);
        $newId = $response->json('data.id');
        $this->assertNotNull($newId);
        $response->assertHeader('Location', url("/api/v1/peminjamans/{$newId}"));
        $this->assertEquals('dipinjam', $response->json('data.status'));

        // Stok buku harus berkurang menjadi 0 secara atomik
        $this->assertEquals(0, $this->bukuStokSatu->fresh()->stok);

        // HATEOAS: Pada peminjaman berstatus 'dipinjam', harus tersedia link kembalikan_buku
        $this->assertArrayHasKey('kembalikan_buku', $response->json('data._links'));
    }

    public function test_it_rejects_borrowing_when_stock_is_exhausted_with_422(): void
    {
        $payload = [
            'buku_id' => $this->bukuStokHabis->id,
            'nim_peminjam' => '12S22002',
            'nama_peminjam' => 'Ucuk Silalahi',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', $payload);

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $this->assertStringContainsString('stok', strtolower($response->json('detail')));
        $this->assertEquals(0, $this->bukuStokHabis->fresh()->stok);
    }

    public function test_it_rejects_duplicate_active_loan_for_same_student_and_book_with_409(): void
    {
        // Peminjaman pertama berhasil
        $payload = [
            'buku_id' => $this->bukuStokSatu->id,
            'nim_peminjam' => '12S22003',
            'nama_peminjam' => 'Tigor Siregar',
        ];

        $res1 = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', $payload);
        $res1->assertStatus(201);

        // Naikkan stok sementara untuk menguji validasi duplicate loan
        $this->bukuStokSatu->increment('stok', 5);

        // Peminjaman kedua oleh NIM yang sama untuk buku yang sama saat pinjaman pertama belum dikembalikan
        $res2 = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', $payload);

        $res2->assertStatus(409);
        $res2->assertHeader('Content-Type', 'application/problem+json');
        $this->assertEquals('Duplicate Loan Conflict', $res2->json('title'));
    }

    public function test_it_returns_book_and_increments_stock_with_acid_transaction(): void
    {
        // 1. Catat peminjaman
        $pinjam = Peminjaman::create([
            'kode_pinjam' => 'PINJ-TEST-RETURN-001',
            'buku_id' => $this->bukuStokSatu->id,
            'nim_peminjam' => '12S22004',
            'nama_peminjam' => 'Debora',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);
        $this->bukuStokSatu->decrement('stok'); // stok jadi 0

        $this->assertEquals(0, $this->bukuStokSatu->fresh()->stok);

        // 2. Kembalikan buku via API PATCH
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/peminjamans/{$pinjam->id}/kembalikan");

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'dikembalikan');
        $this->assertNotNull($response->json('data.tanggal_kembali'));

        // Stok buku harus bertambah kembali (+1) menjadi 1
        $this->assertEquals(1, $this->bukuStokSatu->fresh()->stok);

        // HATEOAS: Pada peminjaman yang sudah 'dikembalikan', link kembalikan_buku tidak boleh muncul
        $this->assertArrayNotHasKey('kembalikan_buku', $response->json('data._links'));

        // 3. Uji coba pengembalian kedua kali -> Harus ditolak 409 Conflict
        $resDuplicateReturn = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/peminjamans/{$pinjam->id}/kembalikan");

        $resDuplicateReturn->assertStatus(409);
        $resDuplicateReturn->assertHeader('Content-Type', 'application/problem+json');
        // Stok tidak boleh bertambah lagi
        $this->assertEquals(1, $this->bukuStokSatu->fresh()->stok);
    }

    public function test_it_lists_peminjamans_with_filters_and_pagination(): void
    {
        Peminjaman::create([
            'kode_pinjam' => 'PINJ-FILTER-001',
            'buku_id' => $this->bukuStokSatu->id,
            'nim_peminjam' => '12S22099',
            'nama_peminjam' => 'Mahasiswa 99',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/peminjamans?nim=12S22099&status=dipinjam');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'kode_pinjam', 'nim_peminjam', 'buku', '_links'],
            ],
            'meta' => ['current_page', 'total_items'],
        ]);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('PINJ-FILTER-001', $response->json('data.0.kode_pinjam'));
    }
}
