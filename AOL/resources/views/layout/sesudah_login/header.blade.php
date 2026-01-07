<style>
    #mobileMenu {
        top: 100px;
        height: calc(100vh - 110px);
        z-index: 2000;
        visibility: visible !important;
    }

    .offcanvas-backdrop {
        z-index: 1500;
    }

    #mobileMenu .menu-item {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        margin-bottom: 12px;
        border-radius: 10px;
        background: #fff;
        text-decoration: none;
        color: #e63939;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(0,0,0,.08);
    }

    #mobileMenu .menu-sell{
        display: flex;
        align-items: center;
        padding-bottom: 16px;
        margin-bottom: 12px;
        background: #fff;
        text-decoration: none;
        font-style: italic;
        color: #e63939;
        font-weight: 700;
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

<header style="background-color:#e63939; position: fixed; top: 0; left:0; width:100%; z-index: 999;">

    <div class="container d-flex justify-content-between align-items-center py-1 text-white small">

        <div class="d-none d-md-flex">
            <a href="#" class="text-white text-decoration-none fw-semibold me-2">
                @lang('messages.sales_centre')
            </a> |
            <a href="{{ route('start.selling') }}" class="text-white text-decoration-none fw-semibold ms-2">
                @lang('messages.start_selling')
            </a>
        </div>

        <button class="btn text-white d-md-none"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileMenu">
            ☰
        </button>

        <div class="d-none d-md-flex gap-3 align-items-center">

            <form action="{{ route('locale.set', 'dummy') }}" method="POST" id="langForm">
                @csrf 
                <select class="form-select form-select-sm bg-transparent text-white border-0" style="width: auto" onchange="submitLang(this.value)">
                    <option value="id" {{ app()->getLocale() === 'id' ? 'selected' : '' }}>
                        🇮🇩 ID
                    </option>
                    <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>
                        🇺🇸 EN
                    </option>
                </select>
            </form>
            <a class="text-white text-decoration-none align-items-center gap-1 d-none d-md-flex" href="#">
                <img src="{{ asset('asset/images/sebelum_login/Icon.png') }}" width="16">
                @lang('messages.notification')
            </a>

            <a class="text-white text-decoration-none align-items-center gap-1 d-none d-md-flex" href="#">
                <img src="{{ asset('asset/images/sebelum_login/Help Icon.png') }}" width="16">
                @lang('messages.help')
            </a>

            <a href="{{ route('profile') }}"
               class="text-white text-decoration-none align-items-center gap-2 d-none d-md-flex">
                <img src="{{ Auth::user()->profile_picture
                    ? asset('storage/' . Auth::user()->profile_picture)
                    : asset('asset/images/sesudah_login/defaultprofile.jpg') }}"
                     class="rounded-circle border border-white"
                     width="30" height="30"
                     style="object-fit:cover">
                <span class="fw-semibold">
                    {{ Auth::user()->name ?? Auth::user()->username ?? 'User' }}
                </span>
            </a>
        </div>

        <div class="d-flex d-md-none">
            <a href="{{ route('home') }}">
                <img src="{{ asset('asset/images/sebelum_login/Logobgputih.png') }}"
                    height="45" class="d-md-none">
            </a>
        </div>
    </div>

    <div class="container d-none d-md-flex justify-content-between align-items-center py-2">

        <a href="{{ route('home') }}">
            <img src="{{ asset('asset/images/sebelum_login/Logobgputih.png') }}"
                height="100" class="d-none d-md-block">
        </a>
        
        <div class="flex-grow-1 mx-4 d-none d-md-block">
            <form class="input-group"
                  action="{{ request()->routeIs('flashsales*') ? route('flashsales') : route('products') }}"
                  method="GET">
                <input type="text" class="form-control" name="q"
                       placeholder="Search..." value="{{ request('q') }}">
                <button class="btn btn-light border" type="submit">
                    <img src="{{ asset('asset/images/sebelum_login/search.png') }}" width="18">
                </button>
            </form>
        </div>

        <a href="{{ route('cart.index') }}" class="d-none d-md-block">
            <img src="{{ asset('asset/images/sebelum_login/Shopping cart.png') }}" height="32">
        </a>

    </div>

    <div class="container d-md-none pb-2">
        <form class="input-group"
              action="{{ request()->routeIs('flashsales*') ? route('flashsales') : route('products') }}"
              method="GET">
            <input type="text" class="form-control form-control-sm"
                   name="q" placeholder="Search..." value="{{ request('q') }}">
            <button class="btn btn-light border" type="submit">
                <img src="{{ asset('asset/images/sebelum_login/search.png') }}" width="16">
            </button>
        </form>
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
        <a href="{{ route('start.selling') }}" class="menu-sell">
            @lang('messages.start_selling')
        </a>
        <a href="{{ route('profile') }}" class="menu-item">@lang('messages.profile')</a>
        <a href="{{ route('cart.index') }}" class="menu-item">@lang('messages.cart')</a>
        <a href="#" class="menu-item">@lang('messages.notification')</a>
        <a href="#" class="menu-item">@lang('messages.help')</a>
        <form action="{{ route('logout') }}" method="POST" class="pt-4">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
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
