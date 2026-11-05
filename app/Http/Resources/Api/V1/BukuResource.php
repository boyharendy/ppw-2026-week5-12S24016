<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAvailable = $this->stok > 0;

        $links = [
            'self' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'GET',
            ],
            'update_full' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PUT',
            ],
            'update_partial' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PATCH',
            ],
            'delete' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'DELETE',
            ],
        ];

        // HATEOAS: Aksi 'pinjam_buku' HANYA muncul jika stok > 0
        if ($isAvailable) {
            $links['pinjam_buku'] = [
                'href' => url('/api/v1/peminjamans'),
                'method' => 'POST',
                'description' => 'Pinjam buku ini menggunakan payload JSON (buku_id, nim_peminjam, nama_peminjam, durasi_hari)',
            ];
        }

        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'penulis' => $this->penulis,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => (int) $this->tahun_terbit,
            'stok' => (int) $this->stok,
            'status_stok' => $this->stok > 0 ? 'Tersedia' : 'Habis',
            'sinopsis' => $this->sinopsis,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'total_peminjaman' => $this->whenCounted('peminjamans', fn () => $this->peminjamans_count, 0),
            'riwayat_peminjaman' => $this->whenLoaded('peminjamans'),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => $links,
        ];
    }
}
