<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'isbn',
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'kategori_id',
        'stok',
        'sinopsis',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function getStatusStokAttribute()
    {
        return $this->stok > 0 ? 'Tersedia' : 'Habis';
    }
}
