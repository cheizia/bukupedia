<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bukupedia</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/dashboard">
            Bukupedia
        </a>

        @auth
            <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                @csrf

                <button type="submit" class="btn btn-light btn-sm">
                    Logout
                </button>
            </form>
        @endauth
    </div>
</nav>

<main class="container mt-4">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@yield('script')

</body>
</html>