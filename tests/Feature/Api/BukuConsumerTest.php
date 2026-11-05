<?php

namespace Tests\Feature\Api;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BukuConsumerTest extends TestCase
{
    use RefreshDatabase;

    private Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $kategori = Kategori::create(['kode_kategori' => 'INF', 'nama_kategori' => 'Informatika']);
        $this->buku = Buku::create([
            'isbn' => '9780132350884',
            'judul' => 'Clean Code',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2008,
            'kategori_id' => $kategori->id,
            'stok' => 5,
        ]);
    }

    public function test_it_consumes_openlibrary_api_and_returns_enriched_metadata(): void
    {
        $isbnKey = "ISBN:{$this->buku->isbn}";
        Http::fake([
            'openlibrary.org/*' => Http::response([
                $isbnKey => [
                    'title' => 'Clean Code',
                    'publish_date' => 'August 1, 2008',
                    'number_of_pages' => 464,
                    'publishers' => [['name' => 'Prentice Hall']],
                    'url' => 'https://openlibrary.org/books/OL112233M/Clean_Code',
                ],
            ], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->buku->id}/referensi-eksternal");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'local_buku_id' => $this->buku->id,
                'isbn' => '9780132350884',
                'title' => 'Clean Code',
                'number_of_pages' => 464,
                'publish_date' => 'August 1, 2008',
            ],
        ]);
    }

    public function test_it_handles_external_book_not_found(): void
    {
        Http::fake([
            'openlibrary.org/*' => Http::response([], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->buku->id}/referensi-eksternal");

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $this->assertEquals('External Metadata Not Found', $response->json('title'));
    }

    public function test_it_handles_external_service_outage_with_502_bad_gateway(): void
    {
        Http::fake([
            'openlibrary.org/*' => Http::response(null, 500),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->buku->id}/referensi-eksternal");

        $response->assertStatus(502);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $this->assertEquals('External Service Unavailable', $response->json('title'));
    }

    public function test_it_returns_404_if_local_book_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/bukus/99999/referensi-eksternal');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
    }
}
