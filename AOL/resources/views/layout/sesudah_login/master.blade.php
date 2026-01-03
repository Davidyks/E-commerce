<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @include('layout.logo')
    <link rel="stylesheet" href=@yield('css')>
    @include('layout.sesudah_login.bootstrap')
</head>

<style>
    body {
        padding-top: 110px;
    }

    @media (min-width: 768px) {
        body {
            padding-top: 170px;
        }
    }
</style>

<body>

    {{-- HEADER --}}
    @include('layout.sesudah_login.header')

    {{-- MAIN CONTENT --}}
    <main class="min-vh-100">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @include('layout.sesudah_login.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
