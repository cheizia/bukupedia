@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Peminjaman Buku</h3>
            <small class="text-muted">Kelola peminjaman dan pengembalian buku</small>
        </div>
        <button class="btn btn-primary" onclick="tambahPeminjaman()">Tambah Peminjaman</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dataPeminjaman">
                        <tr><td colspan="8" class="text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPeminjaman" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPeminjaman">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Anggota</label>
                        <select id="user_id" class="form-select" required>
                            <option value="">Pilih Anggota</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buku</label>
                        <select id="buku_id" class="form-select" required>
                            <option value="">Pilih Buku</option>
                        </select>
                    </div>
                    <div class="alert alert-info mb-0">Masa peminjaman 7 hari. Denda keterlambatan Rp2.000 per hari.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Pinjam</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    loadPeminjaman();
    loadAnggota();
    loadBuku();
});

async function loadPeminjaman() {
    const tbody = document.getElementById('dataPeminjaman');

    try {
        const response = await fetch('/data/riwayat-peminjaman', {
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();
        tbody.innerHTML = '';

        if (!response.ok) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">${result.message}</td></tr>`;
            return;
        }

        const data = (result.data ?? []).filter(item => item.status === 'dipinjam');

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">Tidak ada peminjaman aktif</td></tr>';
            return;
        }

        data.forEach((item, index) => {
            const denda = hitungDenda(item);
            const terlambat = denda > 0;
            const status = terlambat
                ? '<span class="badge bg-danger">Terlambat</span>'
                : '<span class="badge bg-primary">Dipinjam</span>';

            tbody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.user?.nama ?? '-'}</td>
                    <td>${item.buku?.judul ?? '-'}</td>
                    <td>${formatTanggal(item.tanggal_pinjam)}</td>
                    <td>${formatTanggal(item.tanggal_jatuh_tempo)}</td>
                    <td>${status}</td>
                    <td>${formatRupiah(denda)}</td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="kembalikanBuku(${item.id})">Kembalikan</button>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Terjadi kesalahan</td></tr>';
    }
}

function hitungDenda(item) {
    if (item.status === 'dikembalikan') return Number(item.denda ?? 0);

    const jatuhTempo = new Date(item.tanggal_jatuh_tempo);
    jatuhTempo.setHours(0, 0, 0, 0);

    const hariIni = new Date();
    hariIni.setHours(0, 0, 0, 0);

    if (hariIni <= jatuhTempo) return 0;

    const selisih = hariIni - jatuhTempo;
    const terlambatHari = Math.floor(selisih / (1000 * 60 * 60 * 24));

    return terlambatHari * 2000;
}

function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value ?? 0);
}

function formatTanggal(tanggal) {
    if (!tanggal) return '-';
    return new Date(tanggal).toLocaleDateString('id-ID');
}

async function loadAnggota() {
    const response = await fetch('/data/anggota', {
        headers: { 'Accept': 'application/json' }
    });

    const result = await response.json();
    const select = document.getElementById('user_id');
    select.innerHTML = '<option value="">Pilih Anggota</option>';

    if (!response.ok || !result.data) return;

    result.data.forEach(user => {
        select.innerHTML += `<option value="${user.id}">${user.nama} - ${user.user_id}</option>`;
    });
}

async function loadBuku() {
    const response = await fetch('/data/buku', {
        headers: { 'Accept': 'application/json' }
    });

    const result = await response.json();
    const select = document.getElementById('buku_id');
    select.innerHTML = '<option value="">Pilih Buku</option>';

    if (!response.ok || !result.data) return;

    result.data.forEach(buku => {
        if (buku.stok > 0) {
            select.innerHTML += `<option value="${buku.id}">${buku.judul} - Stok: ${buku.stok}</option>`;
        }
    });
}

function tambahPeminjaman() {
    document.getElementById('formPeminjaman').reset();
    loadAnggota();
    loadBuku();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPeminjaman')).show();
}

document.getElementById('formPeminjaman').addEventListener('submit', async function (e) {
    e.preventDefault();

    const response = await fetch('/data/peminjaman', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({
            user_id: document.getElementById('user_id').value,
            buku_id: document.getElementById('buku_id').value
        })
    });

    const result = await response.json();

    if (!response.ok) {
        alert(result.message ?? 'Peminjaman gagal');
        return;
    }

    alert('Buku berhasil dipinjam');
    bootstrap.Modal.getInstance(document.getElementById('modalPeminjaman')).hide();
    loadPeminjaman();
    loadBuku();
});

async function kembalikanBuku(id) {
    if (!confirm('Proses pengembalian buku ini?')) return;

    const response = await fetch(`/data/peminjaman/${id}/kembali`, {
        method: 'PUT',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }
    });

    const result = await response.json();

    if (!response.ok) {
        alert(result.message ?? 'Pengembalian gagal');
        return;
    }

    alert(`${result.message}\nTerlambat: ${result.terlambat_hari} hari\nDenda: ${formatRupiah(result.denda)}`);
    loadPeminjaman();
    loadBuku();
}
</script>
@endsection