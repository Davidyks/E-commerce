<header style="background-color:#e63939; position: fixed; top: 0; left:0; width:100%; z-index: 999;">
    
    <div class="container d-flex justify-content-between align-items-center py-3">

        <a href="{{ route('seller.home') }}" class="text-decoration-none">
            <img src="{{ asset('asset/images/seller/LogoSellerPutih.png') }}" 
                 alt="BuyBuy Seller" 
                 height="50" 
                 style="object-fit: contain;">
        </a>
        <div class="d-flex align-items-center gap-4">
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
            <a href="#" class="text-white text-decoration-none" title="Notifications">
                <img src="{{ asset('asset/images/sebelum_login/Icon.png') }}" width="25">
                <i class="bi bi-bell" style="font-size: 1.5rem;"></i>
            </a>

            <a href="#" class="text-white text-decoration-none" title="Help Center">
                <img src="{{ asset('asset/images/sebelum_login/Help Icon.png') }}" width="25"> 
                <i class="bi bi-question-circle" style="font-size: 1.5rem;"></i>
            </a>

            <div class="d-flex align-items-center gap-2 ms-2">
                <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : asset('asset/images/sesudah_login/defaultprofile.jpg') }}" 
                     alt="Profile" 
                     class="rounded-circle border border-white" 
                     width="32" 
                     height="32" 
                     style="object-fit: cover; border: 2px solid rgba(255,255,255,0.5);">
                
                <span class="text-white fw-semibold">
                    {{ Auth::user()->name ?? Auth::user()->username ?? 'Seller' }}
                </span>
            </div>

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