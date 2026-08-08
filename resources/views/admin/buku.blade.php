@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Data Buku</h3>
            <small class="text-muted">Kelola data buku Bukupedia</small>
        </div>
        <button type="button" class="btn btn-primary" onclick="tambahBuku()">Tambah Buku</button>
    </div>

    <div id="alert"></div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Buku</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Tahun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dataBuku">
                        <tr><td colspan="9" class="text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBuku" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formBuku">
                @csrf
                <input type="hidden" id="edit_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" id="judul" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Penulis</label>
                        <input type="text" id="penulis" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Penerbit</label>
                        <input type="text" id="penerbit" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select id="kategori_id" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" id="stok" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" id="tahun_terbit" class="form-control" min="1900" max="{{ date('Y') }}" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let daftarBuku = [];

document.addEventListener('DOMContentLoaded', function () {
    loadKategori();
    loadBuku();
});

async function loadBuku() {
    const tbody = document.getElementById('dataBuku');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center">Memuat data...</td></tr>';

    try {
        const response = await fetch('/data/buku', {
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (!response.ok) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Gagal mengambil data buku</td></tr>';
            return;
        }

        daftarBuku = result.data ?? [];
        tbody.innerHTML = '';

        if (daftarBuku.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">Belum ada data buku</td></tr>';
            return;
        }

        daftarBuku.forEach((buku, index) => {
            tbody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${buku.buku_id ?? '-'}</td>
                    <td>${buku.judul ?? '-'}</td>
                    <td>${buku.penulis ?? '-'}</td>
                    <td>${buku.penerbit ?? '-'}</td>
                    <td>${buku.kategori?.nama_kategori ?? '-'}</td>
                    <td>${buku.stok ?? 0}</td>
                    <td>${buku.tahun_terbit ?? '-'}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editBuku(${buku.id})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="hapusBuku(${buku.id})">Hapus</button>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Terjadi kesalahan</td></tr>';
    }
}

async function loadKategori() {
    const select = document.getElementById('kategori_id');

    try {
        const response = await fetch('/data/kategori', {
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();
        select.innerHTML = '<option value="">Pilih Kategori</option>';

        if (!response.ok || !result.data) return;

        result.data.forEach(kategori => {
            select.innerHTML += `<option value="${kategori.id}">${kategori.nama_kategori}</option>`;
        });
    } catch (error) {
        console.error('Gagal mengambil kategori:', error);
    }
}

function tambahBuku() {
    document.getElementById('formBuku').reset();
    document.getElementById('edit_id').value = '';
    document.getElementById('modalTitle').innerText = 'Tambah Buku';
    document.getElementById('btnSimpan').innerText = 'Simpan';

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBuku'));
    modal.show();
}

async function editBuku(id) {
    const buku = daftarBuku.find(item => item.id == id);

    if (!buku) {
        alert('Data buku tidak ditemukan');
        return;
    }

    await loadKategori();

    document.getElementById('edit_id').value = buku.id;
    document.getElementById('judul').value = buku.judul;
    document.getElementById('penulis').value = buku.penulis;
    document.getElementById('penerbit').value = buku.penerbit;
    document.getElementById('kategori_id').value = buku.kategori_id;
    document.getElementById('stok').value = buku.stok;
    document.getElementById('tahun_terbit').value = buku.tahun_terbit;
    document.getElementById('modalTitle').innerText = 'Edit Buku';
    document.getElementById('btnSimpan').innerText = 'Update';

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBuku'));
    modal.show();
}

document.getElementById('formBuku').addEventListener('submit', async function (e) {
    e.preventDefault();

    const id = document.getElementById('edit_id').value;
    const data = {
        judul: document.getElementById('judul').value,
        penulis: document.getElementById('penulis').value,
        penerbit: document.getElementById('penerbit').value,
        kategori_id: document.getElementById('kategori_id').value,
        stok: document.getElementById('stok').value,
        tahun_terbit: document.getElementById('tahun_terbit').value
    };

    const url = id ? `/data/buku/${id}` : '/data/buku';
    const method = id ? 'PUT' : 'POST';
    const btnSimpan = document.getElementById('btnSimpan');

    btnSimpan.disabled = true;
    btnSimpan.innerText = 'Menyimpan...';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            let pesan = result.message ?? 'Gagal menyimpan buku';

            if (result.errors) {
                pesan = Object.values(result.errors).flat().join('\n');
            }

            alert(pesan);
            return;
        }

        alert(id ? 'Buku berhasil diperbarui' : 'Buku berhasil ditambahkan');

        document.getElementById('formBuku').reset();
        document.getElementById('edit_id').value = '';

        const modal = bootstrap.Modal.getInstance(document.getElementById('modalBuku'));
        modal.hide();

        loadBuku();
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan saat menyimpan buku');
    } finally {
        btnSimpan.disabled = false;
        btnSimpan.innerText = 'Simpan';
    }
});

async function hapusBuku(id) {
    if (!confirm('Yakin ingin menghapus buku ini?')) return;

    try {
        const response = await fetch(`/data/buku/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        });

        const result = await response.json();

        if (!response.ok) {
            alert(result.message ?? 'Gagal menghapus buku');
            return;
        }

        alert(result.message ?? 'Buku berhasil dihapus');
        loadBuku();
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan saat menghapus buku');
    }
}
</script>
@endsection