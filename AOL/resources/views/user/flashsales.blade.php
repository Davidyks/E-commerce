@extends('layout.sesudah_login.master') 
@section('title', 'Flashsales')
@section('css', 'css/flashsales.css')
@section('content')
<div class="container-fluid">
    <div class="mx-auto py-4 rounded shadow-sm d-flex justify-content-between" style="background: #fff; width: 95%;">
        <h4 class="fw-bold mb-0 ms-4" style="color:#e63939">
            All Flashsales
        </h4>
        <a class="mb-0 me-4 text-decoration-none" style="color: #e63939; font-size:20px; font-weight: 600;" href="{{ route('home') }}">
            Back
        </a>
    </div>

    <div class="category-list mx-auto">
        @foreach ($categories as $c)
            @php
                $isActive = request('category') == $c->id
            @endphp
            <a href="{{ $isActive ? route('flashsales') : route('flashsales', array_merge(request()->query(), ['category' => $c->id])) }}" class="category-item {{ $isActive ? 'active' : '' }}">
                <img src="{{ $c->category_image }}">
                <div>{{ $c->category_name }}</div>
            </a>
        @endforeach
    </div>

    <div class="flashsale-container">
        <div class="flashsale-header">
            <span class="flashsale-title d-flex align-items-center">
                Flashsales
                @if(request('q'))
                    <span class="text-muted ms-2" style="font-size: 16px;">
                        | Showing results for "<b>{{ request('q') }}</b>"
                    </span>
                @endif
            </span>
        </div>

        @if ($flashsales->isNotEmpty())
            <div class="flashsale-items">
                @foreach ($flashsales as $f)
                    <div class="flashsale-card">
                        <a href="{{ route('products.detail', parameters: $f->product->id ?? $f->variant->product_id) }}" class="text-decoration-none" style="color: black">
                        <div class="position-relative">
                            <div class="image-wrapper">
                                <img src="{{ $f->product->product_image ?? $f->variant->image ?? asset('asset/images/sesudah_login/shirt.jpg') }}" alt="Product">
                            </div>
                            <span class="flashsale-stock">{{ $f->flash_stock }}/{{ $f->initial_stock }} left</span>
                            <span class="flashsale-timer" data-end-time="{{ $f->end_time }}"></span>
                        </div>
                        <p class="flashsale-name">{{ $f->variant ? $f->variant->product->name : $f->product->name }}
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
                                    ★ {{ number_format( $f->product->rating ?? $f->variant->product->rating,1) }}
                                </div>
                            </div>
                            <a class="restricted-btn" href="{{ route('products.detail', $f->product->id ?? $f->variant->product_id) }}">See Detail</a>
                        </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-3">
                <p class="fw-bold mb-1" style="color:#e63939; font-size:18px;">
                    Flashsales belum ada
                </p>
                <p class="text-muted mb-0" style="margin-top: -3px">
                    Nantikan promo menarik dalam waktu dekat...
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