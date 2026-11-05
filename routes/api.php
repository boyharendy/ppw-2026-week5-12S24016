<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthTokenController;

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

    // =========================================================================
    // 2. ENDPOINT TERPROTEKSI STATELESS BEARER TOKEN (auth:sanctum)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);
    });
});
