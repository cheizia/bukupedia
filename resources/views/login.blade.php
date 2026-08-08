@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Login Bukupedia</h3>

                <div id="alert" class="alert alert-danger d-none"></div>

                <form id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" placeholder="Masukkan email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="password" class="form-control" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <div class="text-center mt-3">
                    <span>Belum punya akun?</span>
                    <a href="{{ route('register') }}" class="text-decoration-none">Register</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const alertBox = document.getElementById('alert');
    alertBox.classList.add('d-none');

    const response = await fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        })
    });

    const result = await response.json();

    if (!response.ok) {
        alertBox.textContent = result.message ?? 'Email atau password salah';
        alertBox.classList.remove('d-none');
        return;
    }

    window.location.href = '/dashboard';
});
</script>
@endsection