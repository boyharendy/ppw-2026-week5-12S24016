<?php

namespace Tests\Feature\Soap;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibrarySoapTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_serves_valid_wsdl_contract(): void
    {
        $response = $this->get('/soap/sipus.wsdl');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->assertStringContainsString('<definitions name="LibrarySoapService"', $response->getContent());
        $this->assertStringContainsString('CheckClearanceStatus', $response->getContent());
    }

    public function test_it_returns_soap_clearance_status_cleared_when_no_active_loans(): void
    {
        $xmlPayload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:m="http://sipus.del.ac.id/soap/sipus">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S21001</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call(
            'POST',
            '/soap/sipus',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/xml; charset=utf-8'],
            $xmlPayload
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $content = $response->getContent();
        $this->assertStringContainsString('<m:nim_peminjam>12S21001</m:nim_peminjam>', $content);
        $this->assertStringContainsString('<m:active_loans>0</m:active_loans>', $content);
        $this->assertStringContainsString('<m:is_cleared>true</m:is_cleared>', $content);
    }

    public function test_it_returns_soap_clearance_status_not_cleared_when_active_loans_exist(): void
    {
        $kategori = Kategori::create(['kode_kategori' => 'INF', 'nama_kategori' => 'Informatika']);
        $buku = Buku::create([
            'isbn' => '9781234567890',
            'judul' => 'Pemrograman Web',
            'penulis' => 'Dosen PPW',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 5,
        ]);

        Peminjaman::create([
            'kode_pinjam' => 'PINJ-20261105-001',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S21002',
            'nama_peminjam' => 'Ucuk Silalahi',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);

        $xmlPayload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:m="http://sipus.del.ac.id/soap/sipus">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S21002</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call(
            'POST',
            '/soap/sipus',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/xml; charset=utf-8'],
            $xmlPayload
        );

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('<m:nim_peminjam>12S21002</m:nim_peminjam>', $content);
        $this->assertStringContainsString('<m:active_loans>1</m:active_loans>', $content);
        $this->assertStringContainsString('<m:is_cleared>false</m:is_cleared>', $content);
        $this->assertStringContainsString('<m:nama_peminjam>Ucuk Silalahi</m:nama_peminjam>', $content);
    }

    public function test_it_returns_soap_fault_on_empty_nim_or_missing_tag(): void
    {
        $xmlPayload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:m="http://sipus.del.ac.id/soap/sipus">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam></m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call(
            'POST',
            '/soap/sipus',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/xml; charset=utf-8'],
            $xmlPayload
        );

        $response->assertStatus(500);
        $content = $response->getContent();
        $this->assertStringContainsString('<soap:Fault>', $content);
        $this->assertStringContainsString('<faultcode>soap:Client</faultcode>', $content);
    }

    public function test_it_returns_soap_fault_on_malformed_xml(): void
    {
        $response = $this->call(
            'POST',
            '/soap/sipus',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/xml; charset=utf-8'],
            '<invalid-xml'
        );

        $response->assertStatus(500);
        $content = $response->getContent();
        $this->assertStringContainsString('<soap:Fault>', $content);
    }
}
