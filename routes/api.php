<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamController;

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) { return $request->user(); }); 
    Route::post('/logout', [ AuthController::class, 'logout' ]);
    Route::get('/buku', [ BukuController::class, 'index' ]); 
    Route::get('/buku/{id}', [ BukuController::class, 'show' ]);
    Route::middleware('role:admin')->group(function () { 
        Route::post('/buku', [ BukuController::class, 'store' ]); 
        Route::put('/buku/{id}', [ BukuController::class, 'update' ]); 
        Route::delete('/buku/{id}', [ BukuController::class, 'destroy' ]);
        Route::get('/kategori', [ KategoriController::class, 'index' ]);
        Route::get('/kategori/{id}', [ KategoriController::class, 'show' ]);
        Route::post('/kategori', [ KategoriController::class, 'store' ]); 
        Route::put('/kategori/{id}', [ KategoriController::class, 'update' ]); 
        Route::delete('/kategori/{id}', [ KategoriController::class, 'destroy' ]);
    });
    Route::post('/peminjaman/pinjam', [ PeminjamController::class, 'pinjam' ])->middleware('role:anggota');
    Route::get('/peminjaman/riwayat', [ PeminjamController::class, 'riwayat' ]);
    Route::put('/peminjaman/{id}/kembali', [ PeminjamController::class, 'kembali' ])->middleware('role:admin,petugas');
});
