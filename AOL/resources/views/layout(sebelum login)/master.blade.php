<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuyBuy.com</title>

    <!-- @include('layout(sebelum login).bootstrap') -->
    <link rel="stylesheet" href="{{ asset('bootstrap5.2/css/bootstrap.min.css') }}">

</head>

<body>

    {{-- HEADER --}}
    @include('layout(sebelum login).header')

    {{-- MAIN CONTENT --}}
    <main class="min-vh-100">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @include('layout(sebelum login).footer')

    <!-- Bootstrap JS -->
    <script src="{{ asset('css/bootstrap5.2/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>
