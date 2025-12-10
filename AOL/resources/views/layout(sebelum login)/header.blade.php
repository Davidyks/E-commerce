<header class="bg-danger">

    <!-- Top bar -->
    <div class="container d-flex justify-content-between py-1 text-white small">
        <div>
            <a href="#" class="text-white text-decoration-none fw-semibold me-2">Sales Centre</a> |
            <a href="#" class="text-white text-decoration-none fw-semibold ms-2">Start Selling</a>
        </div>

        <div class="d-flex gap-3 align-items-center">
            <a class="text-white text-decoration-none d-flex align-items-center gap-1" href="#">
                <img src="{{ asset('img/Icon.png') }}" width="16"> Notification
            </a>

            <a class="text-white text-decoration-none d-flex align-items-center gap-1" href="#">
                <img src="{{ asset('img/Help Icon.png') }}" width="16"> Help
            </a>

            <a class="text-white text-decoration-none" href="#">Login</a>|
            <!-- <a class="text-white text-decoration-none" href="#">|</a> -->
            <a class="text-white text-decoration-none" href="#">Register</a>

        </div>
    </div>

    <!-- Main Header -->
    <div class="container py-3 d-flex justify-content-between align-items-center">

        <!-- Logo -->
        <a href="#">
            <img src="{{ asset('img/Logobgputih.png') }}" height="100">
        </a>

        <!-- Search -->
        <div class="flex-grow-1 mx-4">
            <form class="input-group">
                <input type="text" class="form-control" placeholder="Search">
                <button class="btn btn-light border" type="submit">
                    <img src="{{ asset('img/search.png') }}" width="18">
                </button>
            </form>
            <div class="small text-white ms-1 mt-1">Suggestion</div>
        </div>

        <!-- Cart -->
        <a href="#" class="">
            <img src="{{ asset('img/Shopping cart.png') }}" height="32">
        </a>

    </div>
</header>
