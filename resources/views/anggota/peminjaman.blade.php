@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <h3 class="mb-0">Peminjaman Buku</h3>
        <small class="text-muted">Pilih buku yang ingin Anda pinjam</small>
    </div>

    <div class="alert alert-info">
        Maksimal 3 buku aktif. Masa peminjaman 7 hari. Denda keterlambatan Rp2.000 per hari.
    </div>

    <div class="row mb-3">
        <div class="col-md-5">
            <input type="text" id="search" class="form-control" placeholder="Cari judul atau penulis...">
        </div>
        <div class="col-md-4 mt-2 mt-md-0">
            <span class="badge bg-primary fs-6">
                Pinjaman Aktif: <span id="jumlahPinjaman">0</span> / 3
            </span>
        </div>
    </div>

    <div id="alert"></div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dataBuku">
                        <tr><td colspan="8" class="text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form id="csrfForm" class="d-none">
    @csrf
</form>
@endsection

@section('script')
<script>
let daftarBuku = [];
let bukuSedangDipinjam = [];
let jumlahPinjamanAktif = 0;

document.addEventListener('DOMContentLoaded', function () {
    loadData();
    document.getElementById('search').addEventListener('input', tampilkanBuku);
});

async function loadData() {
    await loadRiwayat();
    await loadBuku();
}

async function loadRiwayat() {
    try {
        const response = await fetch('/data/riwayat-peminjaman', {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();

        if (!response.ok) return;

        const aktif = (result.data ?? []).filter(item => item.status === 'dipinjam');
        jumlahPinjamanAktif = aktif.length;
        bukuSedangDipinjam = aktif.map(item => Number(item.buku_id));
        document.getElementById('jumlahPinjaman').innerText = jumlahPinjamanAktif;
    } catch (error) {
        console.error(error);
    }
}

async function loadBuku() {
    const tbody = document.getElementById('dataBuku');

    try {
        const response = await fetch('/data/buku', {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();

        if (!response.ok) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Gagal mengambil data buku</td></tr>';
            return;
        }

        daftarBuku = result.data ?? [];
        tampilkanBuku();
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Terjadi kesalahan</td></tr>';
    }
}

function tampilkanBuku() {
    const tbody = document.getElementById('dataBuku');
    const search = document.getElementById('search').value.toLowerCase();

    const data = daftarBuku.filter(buku => {
        const judul = (buku.judul ?? '').toLowerCase();
        const penulis = (buku.penulis ?? '').toLowerCase();
        return judul.includes(search) || penulis.includes(search);
    });

    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Buku tidak ditemukan</td></tr>';
        return;
    }

    data.forEach((buku, index) => {
        const sedangDipinjam = bukuSedangDipinjam.includes(Number(buku.id));
        let tombol = '';

        if (sedangDipinjam) {
            tombol = '<button class="btn btn-secondary btn-sm" disabled>Sedang Dipinjam</button>';
        } else if (buku.stok <= 0) {
            tombol = '<button class="btn btn-secondary btn-sm" disabled>Stok Habis</button>';
        } else if (jumlahPinjamanAktif >= 3) {
            tombol = '<button class="btn btn-secondary btn-sm" disabled>Maksimal 3 Buku</button>';
        } else {
            tombol = `<button class="btn btn-primary btn-sm" onclick="pinjamBuku(${buku.id}, '${escapeQuote(buku.judul)}')">Pinjam</button>`;
        }

        tbody.innerHTML += `
            <tr>
                <td>${index + 1}</td>
                <td>${buku.buku_id ?? '-'}</td>
                <td>${buku.judul ?? '-'}</td>
                <td>${buku.penulis ?? '-'}</td>
                <td>${buku.penerbit ?? '-'}</td>
                <td>${buku.kategori?.nama_kategori ?? '-'}</td>
                <td>${buku.stok ?? 0}</td>
                <td>${tombol}</td>
            </tr>
        `;
    });
}

async function pinjamBuku(id, judul) {
    if (!confirm(`Pinjam buku "${judul}"?`)) return;

    try {
        const response = await fetch('/data/pinjam', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('#csrfForm input[name="_token"]').value
            },
            body: JSON.stringify({ buku_id: id })
        });

        const result = await response.json();

        if (!response.ok) {
            showAlert(result.message ?? 'Gagal meminjam buku', 'danger');
            return;
        }

        showAlert('Buku berhasil dipinjam. Jatuh tempo 7 hari dari tanggal peminjaman.', 'success');
        await loadData();
    } catch (error) {
        console.error(error);
        showAlert('Terjadi kesalahan saat meminjam buku', 'danger');
    }
}

function showAlert(message, type) {
    document.getElementById('alert').innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function escapeQuote(text) {
    return String(text ?? '').replace(/'/g, "\\'");
}
</script>
@endsection