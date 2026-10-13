<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'peminjamans';

    // Kolom-kolom yang diizinkan untuk diisi data secara massal (Mass Assignment)
    protected $fillable = [
        'kode_pinjam',
        'buku_id',
        'nim_peminjam',
        'nama_peminjam',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'status',
    ];

    // Relasi: Setiap transaksi peminjaman dimiliki oleh (belongsTo) satu Buku
    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class);
    }
}
