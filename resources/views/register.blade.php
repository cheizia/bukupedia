@extends('layouts.app')

@section('content')
<div class="col-md-5 mx-auto">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h3 class="text-center mb-4">Register Bukupedia</h3>
            <div id="alert" class="alert d-none"></div>

            <form id="registerForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" id="nama" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Daftar</button>

                <div class="text-center mt-3">
                    Sudah punya akun?
                    <a href="{{ route('login') }}">Login</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const response = await fetch('/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({
            nama: document.getElementById('nama').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            password_confirmation: document.getElementById('password_confirmation').value
        })
    });

    const result = await response.json();
    const alertBox = document.getElementById('alert');

    if (!response.ok) {
        let pesan = result.message ?? 'Registrasi gagal';

        if (result.errors) {
            pesan = Object.values(result.errors).flat().join('<br>');
        }

        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = pesan;
        return;
    }

    alertBox.className = 'alert alert-success';
    alertBox.innerHTML = 'Registrasi berhasil, silakan login.';

    setTimeout(() => {
        window.location.href = '/login';
    }, 1000);
});
</script>
@endsection