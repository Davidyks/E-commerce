<header style="background-color:#e63939; position: fixed; top: 0; left:0; width:100%; z-index: 999;">
    
    <div class="container d-flex justify-content-between align-items-center py-3 px-2-md px-4">

        <a href="#" class="text-decoration-none d-none d-md-block">
            <img src="{{ asset('asset/images/seller/LogoSellerPutih.png') }}" 
                 alt="BuyBuy Seller" 
                 height="50" 
                 style="object-fit: contain;">
        </a>
        <a href="{{ route('home') }}" class="text-white fw-bold text-decoration-none d-md-none d-block">
            Back as User
        </a>
        <div class="d-flex align-items-center gap-2">
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

            <a href="{{ route('home') }}" class="text-white fw-bold text-decoration-none d-none d-md-block">
                Back as User
            </a>
        </div>
        
    </div>
</header>

<script>
    function submitLang(lang) {
        const form = document.getElementById('langForm');
        form.action = form.action.replace('dummy', lang);
        form.submit();
    }
</script>