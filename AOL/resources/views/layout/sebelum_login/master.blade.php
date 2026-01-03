<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @include('layout.logo')
    <link rel="stylesheet" href=@yield('css')>
    @include('layout.sebelum_login.bootstrap')
</head>

<style>
    body {
        padding-top: 123px;
    }

    @media (min-width: 768px) {
        body {
            padding-top: 150px;
        }
    }
</style>

<body>

    {{-- HEADER --}}
    @include('layout.sebelum_login.header')

    {{-- MAIN CONTENT --}}
    <main class="min-vh-100">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @include('layout.sebelum_login.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
