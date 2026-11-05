<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BukuApiController;

/*
|--------------------------------------------------------------------------
| API Routes - SIPUS-Del RESTful Web API v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // =========================================================================
    // 1. ENDPOINT PUBLIK
    // =========================================================================
    Route::post('/auth/login', [AuthTokenController::class, 'login']);

    // Katalog Buku Publik
    Route::get('/bukus', [BukuApiController::class, 'index']);
    Route::get('/bukus/{identifier}', [BukuApiController::class, 'show']);

    // =========================================================================
    // 2. ENDPOINT TERPROTEKSI STATELESS BEARER TOKEN (auth:sanctum)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);

        // Mutasi Katalog Buku
        Route::post('/bukus', [BukuApiController::class, 'store']);
        Route::put('/bukus/{id}', [BukuApiController::class, 'update']);
        Route::patch('/bukus/{id}', [BukuApiController::class, 'update']);
        Route::delete('/bukus/{id}', [BukuApiController::class, 'destroy']);
    });
});
