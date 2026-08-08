<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/data/buku', [BukuController::class, 'index']);
    Route::get('/data/kategori', [KategoriController::class, 'index']);

    Route::post('/data/buku', [BukuController::class, 'store']);
    Route::put('/data/buku/{id}', [BukuController::class, 'update']);
    Route::delete('/data/buku/{id}', [BukuController::class, 'destroy']);

});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/admin/buku', function () {
    return view('admin.buku');
})->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/admin/kategori', function () {
        return view('admin.kategori');
    });

    Route::get('/data/kategori', [KategoriController::class, 'index']);
    Route::post('/data/kategori', [KategoriController::class, 'store']);
    Route::put('/data/kategori/{id}', [KategoriController::class, 'update']);
    Route::delete('/data/kategori/{id}', [KategoriController::class, 'destroy']);
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/riwayat', function () {
        abort_unless(auth()->user()->role === 'admin', 403);
        return view('riwayat');
    });
    Route::get('/petugas/riwayat', function () {
        abort_unless(auth()->user()->role === 'petugas', 403);
        return view('riwayat');
    });
    Route::get('/anggota/riwayat', function () {
        abort_unless(auth()->user()->role === 'anggota', 403);
        return view('riwayat');
    });
    Route::get('/data/riwayat-peminjaman', [PeminjamController::class, 'riwayat']);
});

Route::middleware('auth')->group(function () {
    Route::get('/petugas/peminjaman', function () {
        abort_unless(auth()->user()->role === 'petugas', 403);
        return view('petugas.peminjaman');
    });

    Route::get('/data/anggota', [PeminjamController::class, 'daftarAnggota']);
    Route::post('/data/peminjaman', [PeminjamController::class, 'pinjamPetugas']);
    Route::get('/data/riwayat-peminjaman', [PeminjamController::class, 'riwayat']);
    Route::put('/data/peminjaman/{id}/kembali', [PeminjamController::class, 'kembali']);
});

Route::middleware('auth')->group(function () {
    Route::get('/anggota/peminjaman', function () {
        abort_unless(auth()->user()->role === 'anggota', 403);
        return view('anggota.peminjaman');
    });

    Route::get('/data/buku', [BukuController::class, 'index']);
    Route::post('/data/pinjam', [PeminjamController::class, 'pinjam']);
    Route::get('/data/riwayat-peminjaman', [PeminjamController::class, 'riwayat']);
});
