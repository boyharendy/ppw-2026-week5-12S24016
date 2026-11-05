<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\Soap\LibrarySoapController;

/*
|--------------------------------------------------------------------------
| Web Routes - SIPUS-Del
|--------------------------------------------------------------------------
*/

// SOAP Web Services (Legacy Integration)
Route::get('/soap/sipus.wsdl', [LibrarySoapController::class, 'wsdl']);
Route::post('/soap/sipus', [LibrarySoapController::class, 'handle']);

Route::get('/', function () {
    return redirect()->route('buku.index');
});

// Autentikasi Pustakawan (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout (Authenticated Only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rute Publik (Read-Only)
Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');
Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');

// Rute Terproteksi Autentikasi (Mutasi Data: CRUD, Transaksi Sirkulasi)
Route::middleware('auth')->group(function () {
    // Sirkulasi Peminjaman & Pengembalian Buku
    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::post('/peminjaman/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');

    // Manajemen Buku (Mutasi)
    Route::get('/buku/create', [BukuController::class, 'create'])->name('buku.create');
    Route::post('/buku', [BukuController::class, 'store'])->name('buku.store');
    Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])->name('buku.edit');
    Route::put('/buku/{buku}', [BukuController::class, 'update'])->name('buku.update');
    Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])->name('buku.destroy');

    // Manajemen Kategori (Mutasi)
    Route::get('/kategori/create', [KategoriController::class, 'create'])->name('kategori.create');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::get('/kategori/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
});

// Rute Detail Buku & Kategori (Diletakkan setelah create agar tidak bentrok)
Route::get('/buku/{buku}', [BukuController::class, 'show'])->name('buku.show');
Route::get('/kategori/{kategori}', [KategoriController::class, 'show'])->name('kategori.show');
