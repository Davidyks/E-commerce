<header style="background-color:#e63939; position: fixed; top: 0; left:0; width:100%; z-index: 999;">

    <!-- Top bar -->
    <div class="container d-flex justify-content-between py-1 text-white small" style="background-color:#e63939">

        <div>
            <a href="#" class="text-white text-decoration-none fw-semibold me-2">@lang('messages.sales_centre')</a> |
            <a href="{{ route('start.selling') }}" class="text-white text-decoration-none fw-semibold ms-2">@lang('messages.start_selling')</a>
        </div>

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

            <a class="text-white text-decoration-none d-flex align-items-center gap-2" href="{{ route('profile') }}">
                <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : asset('asset/images/sesudah_login/defaultprofile.jpg') }}" 
                    alt="Profile" 
                    class="rounded-circle border border-white" 
                    width="30" 
                    height="30" 
                    style="object-fit: cover;">
                    
                <span class="fw-semibold">
                    {{ Auth::user()->name ?? Auth::user()->username ?? 'User' }}
                </span>
            </a>

        </div>
    </div>

    <!-- Main Header -->
    <div class="container d-flex justify-content-between align-items-center">

        <!-- Logo -->
        <a href="{{ route('home') }}">
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
                <input type="text" class="form-control" placeholder="Search..." name="q" value="{{ request('q') }}">
                <button class="btn btn-light border" type="submit">
                    <img src="{{ asset('asset/images/sebelum_login/search.png') }}" width="18">
                </button>
            </form>
        </div>

        <!-- Cart -->
        <a href="{{ route('cart.index') }}" class="">
            <img src="{{ asset('asset/images/sebelum_login/Shopping cart.png') }}" height="32" class="me-4">
        </a>
    </div>
</header>

<script>
    function submitLang(lang) {
        const form = document.getElementById('langForm');
        form.action = form.action.replace('dummy', lang);
        form.submit();
    }
</script>
