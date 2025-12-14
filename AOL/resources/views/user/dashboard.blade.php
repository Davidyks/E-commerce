@extends('layout.sesudah_login.master') 
@section('title', 'Home')
@section('css', 'css/home.css')
@section('content')
<div class="container-fluid">
    <div class="mx-auto py-4 rounded shadow-sm" style="background: #fff; width: 95%;">
        <h4 class="fw-bold mb-0 ms-4">
            Welcome, 
            <span style="color: #e63939">
                {{ Auth::user()->name ?? Auth::user()->username ?? explode('@', Auth::user()->email)[0] }}
            </span>
        </h4>
    </div>

    @if ($categories->isNotEmpty())
        <div class="category-container">
            <div class="category-title">Category</div>
            <div class="category-list">
                @foreach ($categories as $c)
                    <div class="category-item">
                    <img src={{ $c->category_image }} />
                    <div>{{ $c->category_name }}</div>  
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flashsale-container">
        <div class="flashsale-header">
            <span class="flashsale-title">FlashSale</span>
            @if ($flashsales->isNotEmpty())
                <a href="{{ route('flashsales') }}" class="see-all">See all</a>
            @endif
        </div>
       
        @if ($flashsales->isNotEmpty())
            <div class="flashsale-items">
                @foreach ($flashsales as $f)
                    <div class="flashsale-card">
                        <div class="position-relative">
                            <div class="image-wrapper">
                                <img src="{{ $f->product->product_image ?? $f->variant->image ?? asset('asset/images/sesudah_login/shirt.jpg') }}" alt="Product">
                            </div>
                            <span class="flashsale-stock">{{ $f->flash_stock }}/{{ $f->initial_stock }} left</span>
                            <span class="flashsale-timer" data-end-time="{{ $f->end_time }}"></span>
                        </div>
                        <p class="product-name">{{ $f->variant ? $f->variant->product->name : $f->product->name }}
                            @if ($f->variant)
                                <span class="fw-normal">- {{ $f->variant->variant_name }}</span>
                            @endif
                        </p>
                        <br>
                        <div class="card-bottom">
                            <p class="price fw-bold">${{ $f->flash_price }}</p>
                            <div class="align-items-center d-flex justify-content-between">
                                <p class="before-discount text-muted">${{ $f->variant ? $f->variant->price : $f->product->price }} </p>
                                <div class="flashsale-rate">
                                    ★ {{ $f->product->rating ?? $f->variant->product->rating }}
                                </div>
                            </div>
                            <a class="restricted-btn" href="{{ route('flashsales.detail', $f->id) }}">See Detail</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-3">
                <p class="fw-bold mb-1" style="color:#e63939; font-size:18px;">
                    FlashSale belum ada
                </p>
                <p class="text-muted mb-0" style="margin-top: -3px">
                    Nantikan promo menarik dalam waktu dekat
                </p>
            </div>
        @endif
    </div>

    <div class="flashsale-container">
        <div class="flashsale-header">
            <span class="flashsale-title">Products</span>
            @if ($topProducts->isNotEmpty())
                <a href="{{ route('products') }}" class="see-all">See all</a>
            @endif
        </div>
       
        @if ($topProducts->isNotEmpty())
            <div class="flashsale-items">
                @foreach ($topProducts as $p)
                    <div class="flashsale-card">
                        <div class="position-relative">
                            <div class="image-wrapper">
                                <img src="{{ $p->product_image ?? asset('asset/images/sesudah_login/shirt.jpg') }}" alt="Product">
                            </div>
                        </div>
                        <p class="product-name">{{ $p->name }}</p>
                        <br>
                        <div class="card-bottom">
                            <p class="price fw-bold">
                                 @if ($p->price)
                                    ${{ $p->price }}
                                @else
                                    ${{ $p->min_price }} - ${{ $p->max_price }}
                                @endif
                            </p>
                            <div class="align-items-center d-flex justify-content-between">
                                <p class="before-discount text-muted text-decoration-none">{{ $p->sold_count }} Sold</p>
                                <div class="flashsale-rate">
                                    ★ {{ $p->rating }}
                                </div>
                            </div>
                            <div class="estimate text-muted">
                                <img src="{{ asset('asset/images/sebelum_login/delivery.png') }}" alt="delivery" style="width:28px;height:28px;object-fit:contain">Estimate: {{ $p->delivery_estimate_days }} days
                            </div>
                            <a class="restricted-btn" href="{{ route('products.detail', $p->id) }}">See Detail</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-3">
                <p class="fw-bold mb-1" style="color:#e63939; font-size:18px;">
                    Product belum ada
                </p>
                <p class="text-muted mb-0" style="margin-top: -3px">
                    Barang sedang direstock...
                </p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const timers = document.querySelectorAll('.flashsale-timer');

    timers.forEach(timer => {
        const endTime = new Date(timer.dataset.endTime).getTime();

        const updateTimer = () => {
            const now = new Date().getTime();
            const diff = endTime - now;

            if (diff <= 0) {
                timer.closest('.flashsale-card').remove();
                const container = document.getElementById('flashsale-items');
                if (container && container.children.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-3">
                            <p class="fw-bold mb-1" style="color:#e63939; font-size:18px;">
                                FlashSale belum ada
                            </p>
                            <p class="text-muted mb-0" style="margin-top:-3px">
                                Nantikan promo menarik dalam waktu dekat
                            </p>
                        </div>
                    `;
                }
                return;
            }

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            timer.textContent =
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
        };

        updateTimer();
        setInterval(updateTimer, 1000);
    });
});
</script>
@endsection
