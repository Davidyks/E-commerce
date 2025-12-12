<header style="background-color:#e63939;  position: fixed; top: 0; left:0; width:100%; z-index: 999;">

    <!-- Top bar -->
    <div class="container d-flex justify-content-end py-1 text-white small">
        <div class="d-flex gap-3 align-items-center">
            <a class="text-white text-decoration-none d-flex align-items-center gap-1" href="#">
                <img src="{{ asset('asset/images/sebelum_login/Icon.png') }}" width="16"> Notification
            </a>

            <a class="text-white text-decoration-none d-flex align-items-center gap-1" href="#">
                <img src="{{ asset('asset/images/sebelum_login/Help Icon.png') }}" width="16"> Help
            </a>

            <a class="text-white text-decoration-none fw-bold" href="{{ route('login') }}">Login</a>|
            <a class="text-white text-decoration-none fw-bold" href="{{ route('register') }}">Register</a>
        </div>
    </div>

    <!-- Main Header -->
    <div class="container d-flex justify-content-between align-items-center">

        <!-- Logo -->
        <a href="#">
            <img src="{{ asset('asset/images/sebelum_login/Logobgputih.png') }}" height="100">
        </a>

        <!-- Search -->
        <div class="flex-grow-1 mx-4">
            <form class="input-group">
                <input type="text" class="form-control" placeholder="Search">
                <button class="btn btn-light border" type="submit">
                    <img src="{{ asset('asset/images/sebelum_login/search.png') }}" width="18">
                </button>
            </form>
        </div>

        <!-- Cart -->
        <a href="{{ route('login') }}" class="">
            <img src="{{ asset('asset/images/sebelum_login/Shopping cart.png') }}" height="32" class="me-4">
        </a>
    </div>
</header>
