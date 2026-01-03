<style>
    #mobileMenu {
        top: 123px;
        height: calc(100vh - 123px);
        z-index: 2000;
        visibility: visible !important;
    }

    #mobileMenu .menu-item {
        display: flex;
        align-items: center;
        padding: 8px 16px;
        margin-bottom: 16px;
        border-radius: 8px;
        background: #fff;
        text-decoration: none;
        color: #e63939;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        transition: all .2s ease;
    }

    #mobileMenu .menu-item:active {
        background: rgba(230,57,57,.15);
    }

    @media (hover:hover) {
        #mobileMenu .menu-item:hover {
            transform: translateX(4px);
            background: rgba(230,57,57,.08);
        }
    }
</style>

<header style="background-color:#e63939;  position: fixed; top: 0; left:0; width:100%; z-index: 900;">

    <!-- Dekstop -->
    <div class="d-none d-md-block">
    <!-- Top bar -->
        <div class="container d-flex justify-content-end py-1 text-white small">
            <div class="d-flex gap-3 align-items-center">
                <form action="{{ route('locale.set', 'dummy') }}"
                    method="POST"
                    id="langForm">
                    @csrf
                    <select
                        class="form-select form-select-sm bg-transparent text-white border-0"
                        style="width: auto"
                        onchange="submitLang(this.value)">
                        <option value="id" {{ app()->getLocale() === 'id' ? 'selected' : '' }}>
                            🇮🇩 ID
                        </option>
                        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>
                            🇺🇸 EN
                        </option>
                    </select>
                </form>

                <a class="text-white text-decoration-none d-flex align-items-center gap-1" href="#">
                    <img src="{{ asset('asset/images/sebelum_login/Icon.png') }}" width="16"> @lang('messages.notification')
                </a>

                <a class="text-white text-decoration-none d-flex align-items-center gap-1" href="#">
                    <img src="{{ asset('asset/images/sebelum_login/Help Icon.png') }}" width="16"> @lang('messages.help')
                </a>

                <a class="text-white text-decoration-none fw-bold" href="{{ route('login') }}">@lang('messages.login')</a>|
                <a class="text-white text-decoration-none fw-bold" href="{{ route('register') }}">@lang('messages.register')</a>
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
                <form class="input-group" action="{{ request()->routeIs('flashsales*') ? route('flashsales') : route('products') }}" method="GET">
                    @foreach(request()->query() as $key => $value)
                        @if($key !== 'q')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="text" class="form-control" placeholder="Search..." name="q" value="{{ request('q') }}" >
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
    </div>

    <!-- Mobile -->
    <div class="d-flex d-md-none flex-column p-3">

        <div class="d-flex align-items-center justify-content-between">

            {{-- Hamburger --}}
            <button class="btn text-white"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mobileMenu">
                ☰
            </button>

            <a href="{{ route('home') }}">
                <img src="{{ asset('asset/images/sebelum_login/Logobgputih.png') }}" height="45">
            </a>
        </div>

        <div class="mt-2">
            <form class="input-group"
                  action="{{ request()->routeIs('flashsales*') ? route('flashsales') : route('products') }}"
                  method="GET">
                <input type="text"
                       class="form-control form-control-sm"
                       placeholder="Search..."
                       name="q"
                       value="{{ request('q') }}">
                <button class="btn btn-light border" type="submit">
                    <img src="{{ asset('asset/images/sebelum_login/search.png') }}" width="16">
                </button>
            </form>
        </div>
    </div>

</header>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        <form action="{{ route('locale.set', 'dummy') }}" method="POST" id="langFormMobile" class="mb-4">
            @csrf 
            <label class="small text-muted mb-1">Language</label>
            <select class="form-select form-select-sm bg-transparent text-dark border-0" style="width: auto" onchange="submitLang(this.value)">
                <option value="id" {{ app()->getLocale() === 'id' ? 'selected' : '' }}>
                    🇮🇩 ID
                </option>
                <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>
                    🇺🇸 EN
                </option>
            </select>
        </form>
        <a href="{{ route('login') }}" class="menu-item">Login</a>
        <a href="{{ route('register') }}" class="menu-item">Register</a>
        <a href="{{ route('login') }}" class="menu-item"> Cart </a>
        <a href="#" class="menu-item">Notification</a>
        <a href="#" class="menu-item">Help</a>
    </div>
</div>

<script>
    function submitLang(lang) {
        const activeSelect = event.target;
        const form = activeSelect.closest('form');

        if (!form) return;

        form.action = form.action.replace('dummy', lang);
        form.submit();
    }
</script>
