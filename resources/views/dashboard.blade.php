@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h3>Dashboard Bukupedia</h3>

            <p class="mb-1">
                Selamat datang,
                <strong>{{ Auth::user()->nama }}</strong>
            </p>

            <span class="badge bg-primary">
                {{ ucfirst(Auth::user()->role) }}
            </span>
        </div>
    </div>

    <div class="row g-4">

        {{-- ADMIN --}}
        @if(Auth::user()->role === 'admin')
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Data Buku</h5>
                        <p class="card-text">
                            Tambah, ubah, lihat, dan hapus data buku.
                        </p>

                        <a href="{{ url('/admin/buku') }}"
                           class="btn btn-primary">
                            Kelola Buku
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Kategori Buku</h5>
                        <p class="card-text">
                            Kelola kategori buku perpustakaan.
                        </p>

                        <a href="{{ url('/admin/kategori') }}"
                           class="btn btn-primary">
                            Kelola Kategori
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Riwayat Pinjam Buku</h5>
                        <p class="card-text">
                            Kelola riwayat peminjaman buku.
                        </p>

                        <a href="{{ url('/admin/riwayat') }}"
                           class="btn btn-primary">
                            Kelola Riwayat
                        </a>
                    </div>
                </div>
            </div>
        @endif


        {{-- PETUGAS --}}
        @if(Auth::user()->role === 'petugas')
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Peminjaman Buku</h5>

                        <p class="card-text">
                            Kelola peminjaman dan pengembalian buku.
                        </p>

                        <a href="{{ url('/petugas/peminjaman') }}"
                           class="btn btn-success">
                            Kelola Peminjaman
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Riwayat Pinjam Buku</h5>
                        <p class="card-text">
                            Kelola riwayat peminjaman buku.
                        </p>

                        <a href="{{ url('/petugas/riwayat') }}"
                           class="btn btn-primary">
                            Kelola Riwayat
                        </a>
                    </div>
                </div>
            </div>
        @endif


        {{-- ANGGOTA --}}
        @if(Auth::user()->role === 'anggota')
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Peminjaman Buku</h5>
                    <p>Pilih dan pinjam buku yang tersedia.</p>
                    <a href="{{ url('/anggota/peminjaman') }}" class="btn btn-primary">Pinjam Buku</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Riwayat Peminjaman</h5>
                    <p>Lihat buku yang sedang dan pernah Anda pinjam.</p>
                    <a href="{{ url('/anggota/riwayat') }}" class="btn btn-secondary">Riwayat Saya</a>
                </div>
            </div>
        </div>
    @endif

    </div>

</div>
@endsection

@section('script')
@endsection