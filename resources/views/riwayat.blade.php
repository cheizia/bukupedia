@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <h3 class="mb-0">Riwayat Peminjaman</h3>

        @if(Auth::user()->role === 'peminjam')
            <small class="text-muted">Riwayat peminjaman buku Anda</small>
        @else
            <small class="text-muted">Seluruh riwayat peminjaman buku</small>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="search" class="form-control" placeholder="Cari buku...">
                </div>

                <div class="col-md-3">
                    <select id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="dipinjam">Dipinjam</option>
                        <option value="dikembalikan">Dikembalikan</option>
                        <option value="terlambat">Terlambat</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>

                            @if(Auth::user()->role !== 'peminjam')
                                <th>Peminjam</th>
                            @endif

                            <th>Buku</th>
                            <th>Kategori</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Denda</th>
                        </tr>
                    </thead>

                    <tbody id="dataRiwayat">
                        <tr>
                            <td colspan="9" class="text-center">
                                Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let daftarRiwayat = [];
const role = "{{ Auth::user()->role }}";

document.addEventListener('DOMContentLoaded', function () {
    loadRiwayat();
    document.getElementById('search').addEventListener('input', tampilkanRiwayat);
    document.getElementById('filterStatus').addEventListener('change', tampilkanRiwayat);
});

async function loadRiwayat() {
    try {
        const response = await fetch('/data/riwayat-peminjaman', {
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (!response.ok) {
            alert(result.message ?? 'Gagal mengambil riwayat');
            return;
        }

        daftarRiwayat = result.data ?? [];
        tampilkanRiwayat();
    } catch (error) {
        console.error(error);
    }
}

function tampilkanRiwayat() {
    const tbody = document.getElementById('dataRiwayat');
    const search = document.getElementById('search').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;

    const data = daftarRiwayat.filter(item => {
        const nama = item.user?.nama?.toLowerCase() ?? '';
        const judul = item.buku?.judul?.toLowerCase() ?? '';
        const cocokSearch = nama.includes(search) || judul.includes(search);
        const cocokStatus = !status || item.status === status;
        return cocokSearch && cocokStatus;
    });

    tbody.innerHTML = '';

    if (data.length === 0) {
        const colspan = role === 'peminjam' ? 8 : 9;
        tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center">Belum ada riwayat peminjaman</td></tr>`;
        return;
    }

    data.forEach((item, index) => {
        let badge = 'bg-secondary';
        if (item.status === 'dipinjam') badge = 'bg-primary';
        if (item.status === 'dikembalikan') badge = 'bg-success';
        if (item.status === 'terlambat') badge = 'bg-danger';

        let peminjam = '';

        if (role !== 'peminjam') {
            peminjam = `<td>${item.user?.nama ?? '-'}</td>`;
        }

        tbody.innerHTML += `
            <tr>
                <td>${index + 1}</td>
                ${peminjam}
                <td>${item.buku?.judul ?? '-'}</td>
                <td>${item.buku?.kategori?.nama_kategori ?? '-'}</td>
                <td>${formatTanggal(item.tanggal_pinjam)}</td>
                <td>${formatTanggal(item.tanggal_jatuh_tempo)}</td>
                <td>${formatTanggal(item.tanggal_kembali)}</td>
                <td><span class="badge ${badge}">${item.status ?? '-'}</span></td>
                <td>${formatRupiah(item.denda)}</td>
            </tr>
        `;
    });
}

function formatTanggal(tanggal) {
    if (!tanggal) return '-';
    return new Date(tanggal).toLocaleDateString('id-ID');
}

function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value ?? 0);
}
</script>
@endsection