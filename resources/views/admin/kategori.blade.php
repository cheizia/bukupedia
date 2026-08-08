@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Data Kategori</h3>
            <small class="text-muted">Kelola kategori buku Bukupedia</small>
        </div>
        <button type="button" class="btn btn-primary" onclick="tambahKategori()">Tambah Kategori</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">No</th>
                            <th>Nama Kategori</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dataKategori">
                        <tr><td colspan="3" class="text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formKategori">
                @csrf
                <input type="hidden" id="edit_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" id="nama_kategori" class="form-control" required>
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
let daftarKategori = [];

document.addEventListener('DOMContentLoaded', function () {
    loadKategori();
});

async function loadKategori() {
    const tbody = document.getElementById('dataKategori');
    tbody.innerHTML = '<tr><td colspan="3" class="text-center">Memuat data...</td></tr>';

    try {
        const response = await fetch('/data/kategori', {
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (!response.ok) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal mengambil kategori</td></tr>';
            return;
        }

        daftarKategori = result.data ?? [];
        tbody.innerHTML = '';

        if (daftarKategori.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center">Belum ada kategori</td></tr>';
            return;
        }

        daftarKategori.forEach((kategori, index) => {
            tbody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${kategori.nama_kategori}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editKategori(${kategori.id})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="hapusKategori(${kategori.id})">Hapus</button>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Terjadi kesalahan</td></tr>';
    }
}

function tambahKategori() {
    document.getElementById('formKategori').reset();
    document.getElementById('edit_id').value = '';
    document.getElementById('modalTitle').innerText = 'Tambah Kategori';
    document.getElementById('btnSimpan').innerText = 'Simpan';

    bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalKategori')
    ).show();
}

function editKategori(id) {
    const kategori = daftarKategori.find(item => item.id == id);

    if (!kategori) {
        alert('Kategori tidak ditemukan');
        return;
    }

    document.getElementById('edit_id').value = kategori.id;
    document.getElementById('nama_kategori').value = kategori.nama_kategori;
    document.getElementById('modalTitle').innerText = 'Edit Kategori';
    document.getElementById('btnSimpan').innerText = 'Update';

    bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalKategori')
    ).show();
}

document.getElementById('formKategori').addEventListener('submit', async function (e) {
    e.preventDefault();

    const id = document.getElementById('edit_id').value;
    const data = {
        nama_kategori: document.getElementById('nama_kategori').value
    };

    const url = id ? `/data/kategori/${id}` : '/data/kategori';
    const method = id ? 'PUT' : 'POST';

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
            alert(result.message ?? 'Gagal menyimpan kategori');
            return;
        }

        alert(id ? 'Kategori berhasil diperbarui' : 'Kategori berhasil ditambahkan');

        document.getElementById('formKategori').reset();
        document.getElementById('edit_id').value = '';

        bootstrap.Modal.getInstance(
            document.getElementById('modalKategori')
        ).hide();

        loadKategori();
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan saat menyimpan kategori');
    }
});

async function hapusKategori(id) {
    if (!confirm('Yakin ingin menghapus kategori ini?')) return;

    try {
        const response = await fetch(`/data/kategori/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        });

        const result = await response.json();

        if (!response.ok) {
            alert(result.message ?? 'Gagal menghapus kategori');
            return;
        }

        alert(result.message ?? 'Kategori berhasil dihapus');
        loadKategori();
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan saat menghapus kategori');
    }
}
</script>
@endsection