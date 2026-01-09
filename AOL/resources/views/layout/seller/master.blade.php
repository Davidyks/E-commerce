<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @include('layout.logo')
    @yield('css')
    @include('layout.seller.bootstrap')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @stack('scripts')
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
    @include('layout.seller.header')

    {{-- MAIN CONTENT --}}
    <main class="min-vh-100">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @include('layout.seller.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>
</html>
