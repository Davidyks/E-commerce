@extends('layout.sebelum_login.master')
@section('title', 'Home')
@section('css', 'css/home_before.css')
@section('content')
<div class="container-fluid">
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
                <a href="#" class="see-all">See all</a>
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
                            <div class="flashsale-rate">
                                {{ $f->product->rating ?? $f->variant->product->rating }} <span style="color:#ffb400; font-size: 20px;;">★</span>
                            </div>
                        </div>
                        <p class="product-name">{{ $f->variant ? $f->variant->product->name : $f->product->name }}
                            @if ($f->variant)
                                <span class="fw-normal">- {{ $f->variant->variant_name }}</span>
                            @endif
                        </p>
                        <div class="card-bottom">
                            <p class="price">${{ $f->flash_price }}</p>
                            <p class="before-discount text-muted">${{ $f->variant ? $f->variant->price : $f->product->price }}</p>
                            <a class="restricted-btn" href="#">See Detail</a>
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