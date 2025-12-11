<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href=@yield('css')>
    @include('layout.sebelum_login.bootstrap')
</head>

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
    <script src="{{ asset('css/bootstrap5.2/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>
