<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
    ];

    public function bukus()
    {
        return $this->hasMany(Buku::class);
    }
}
