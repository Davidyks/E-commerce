@extends('layout.sesudah_login.master')
@section('title', 'Products')
@section('css',asset( 'css/productDetail.css'))

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="container py-5">

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <strong>@lang('messages.success')!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- MAIN PRODUCT CARD --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="row g-5">

                    {{-- GAMBAR PRODUK --}}
                    <div class="col-md-5">
                        <div class="border rounded bg-white text-center overflow-hidden position-relative">
                            <img src="{{ $product->product_image ?? asset('asset/images/sesudah_login/shirt.jpg') }}" 
                                alt="{{ $product->name }}" 
                                class="img-fluid w-100 h-100 object-fit-cover"
                                onerror="this.onerror=null;this.src='{{ asset('asset/images/sesudah_login/shirt.jpg') }}';"/>
                        </div>
                    </div>

                    {{-- Detail --}}
                    <div class="col-md-7">
                        <h3 class="fw-bold text-dark mb-2">{{ $product->name }}</h3>

                        {{-- Rating & Sold --}}
                        <div class="d-flex align-items-center mb-3 small">
                            <span class="text-warning me-1">
                                {{ number_format($product->rating_average,1) }} <i class="fas fa-star"></i>
                            </span>
                            <span class="text-muted mx-2">|</span>
                            <span class="text-muted">{{ $product->sold_count }} @lang('messages.sold')</span>
                            <span class="text-muted mx-2">|</span>
                            <span class="text-muted">@lang('messages.stocks'): <span id="product-stock">{{ $product->stock }}</span></span>
                        </div>

                        {{-- Harga --}}
                        <h2 class="text-danger fw-bold mb-1">
                            $ <span id="product-price">
                                {{ $product->activeFlashsale
                                    ? $product->activeFlashsale->flash_price
                                    : $product->display_price
                                }}
                            </span>
                        </h2>
                        @if ($product->activeFlashsale)
                            <p class="text-muted fw-bold text-decoration-line-through m-0" style="margin-top: -3px;">
                                $ {{ $product->display_price }}
                            </p>
                            <p class="mt-1 mb-0">
                                <span class="text-muted fw-medium">Flashsale @lang('messages.stocks'): </span>
                                <span class="text-danger fw-bold">{{ $product->activeFlashsale?->flash_stock }}</span>
                                <span class="text-muted"> @lang('messages.out_of') </span>
                                <span class="text-muted fw-medium">{{ $product->activeFlashsale?->initial_stock }}</span>
                            </p>
                        @endif

                        <div id="flashsale-info" class="d-none" style="margin-top: -3px;">
                            <p class="text-muted m-0 fw-bold text-decoration-line-through">
                                $ <span id="flashsale-before">{{ $product->display_price }}</span>
                            </p>
                            <p class="mt-1 mb-0">
                                <span class="text-muted fw-medium">Flashsale @lang('messages.stocks'): </span>
                                <span class="text-danger fw-bold" id="flashsale-stock"></span>
                                <span class="text-muted"> @lang('messages.out_of') </span>
                                <span class="text-muted fw-medium" id="flashsale-initial"></span>
                            </p>
                        </div>
                        {{-- FORM --}}

                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            {{-- Varian --}}
                            @if ($product->variants->count() > 0)
                                <hr class="text-secondary opacity-25 my-4">

                                <div class="mb-3">
                                    <label class="fw-bold mb-2 text-secondary">@lang('messages.variant')</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($product->variants as $variant)
                                            <input type="radio" class="btn-check variant-radio" name="variant_id"
                                                id="var_{{ $variant->id }}" value="{{ $variant->id }}" 
                                                data-price="{{ $variant->price }}" 
                                                data-stock="{{ $variant->stock }}"
                                                data-flashsale-price="{{ $variant->activeFlashsale?->flash_price }}"
                                                data-flashsale-stock="{{ $variant->activeFlashsale?->flash_stock }}"
                                                data-flashsale-initial="{{ $variant->activeFlashsale?->initial_stock }}">
                                            <label class="btn btn-outline-secondary px-4 py-2 rounded-pill"
                                                for="var_{{ $variant->id }}">
                                                {{ $variant->variant_name }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('variant_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <hr class="text-secondary opacity-25 my-4">

                            {{-- Detail --}}
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark mb-2">@lang('messages.detail_prod')</h6>
                                <hr class="text-secondary opacity-25 my-4">
                                <ul class="list-unstyled text-secondary small mb-2">
                                    <li>• @lang('messages.condition')</li>
                                    <li>• @lang('messages.min_order'): {{ $product->min_order_qty }} @lang('messages.piece')</li>
                                </ul>
                                <div class="text-secondary small mb-0 desc-text">
                                    <span id="product-desc" class="desc-collapsed">{{ $product->description }}</span>
                                    <a href="#" id="toggle-desc" class="text-danger text-decoration-none fw-bold d-inline">
                                        @lang('messages.read_more')
                                    </a>
                                </div>
                            </div>

                            <hr class="text-secondary opacity-25 my-4">
                            {{-- Section 3: Delivery Info --}}
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark mb-2">@lang('messages.delivery')</h6>
                                <hr class="text-secondary opacity-25 my-4">
                                <div class="d-flex align-items-start gap-2 text-secondary small">
                                    <i class="fas fa-truck mt-1 fs-5"></i>
                                    <div>
                                        <span class="fw-bold text-dark">@lang('messages.estimate')
                                            {{ $product->delivery_estimate_days ?? 2 }} @lang('messages.days')</span><br>
                                        @lang('messages.get_voucher').
                                    </div>
                                </div>
                            </div>

                            <hr class="text-secondary my-4">

                            {{-- Action Buttons & Share --}}
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex gap-3 align-items-center me-4">
                                    {{-- Quantity --}}
                                    <div style="width: 70px;">
                                        <input type="number" name="quantity"
                                            value="{{ old('quantity', $product->min_order_qty) }}"
                                            min="{{ $product->min_order_qty }}" class="form-control text-center fw-bold"
                                            placeholder="{{ $product->min_order_qty }}">
                                    </div>

                                    <button type="submit" name="action" value="add_to_cart"
                                        class="btn btn-light text-danger border-danger px-4 py-2 fw-bold"
                                        style="background-color: #ffe6e6;">
                                        <i class="fas fa-cart-plus me-2"></i> @lang('messages.add_cart')
                                    </button>
                                    <button type="submit" 
                                        formaction="{{ route('buy.now', $product->id) }}" 
                                        class="btn btn-danger px-4 py-2 fw-bold">
                                        @lang('messages.buy_now')
                                    </button>
                                </div>
                            </div>
                            @error('quantity')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror

                        </form>
                    </div>
                </div>
            </div>
        </div>


        {{-- SELLER INFO --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    {{-- Logo & Nama Toko --}}
                    <div class="col-md-4 d-flex align-items-center gap-3 border-end">
                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold fs-3"
                            style="width: 65px; height: 65px;">
                            {{ substr($product->seller->store_name ?? 'S', 0, 1) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $product->seller->store_name ?? 'Official Store' }}</h5>
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-store"></i>
                                    @lang('messages.visit')</button>
                            </div>
                        </div>
                    </div>

                    {{-- Stat Toko --}}
                    <div class="col-md-8">
                        <div class="d-flex justify-content-around text-center">
                            <div>
                                <span class="text-secondary small">@lang('messages.products')</span>
                                <h5 class="text-danger fw-bold mb-0">{{ $sellerProducts }}</h5>
                            </div>
                            <div>
                                <span class="text-secondary small">@lang('messages.rating')</span>
                                <h5 class="text-danger fw-bold mb-0">{{ number_format($sellerRating,1) }}</h5>
                            </div>
                            <div>
                                <span class="text-secondary small">@lang('messages.joined')</span>
                                <h5 class="text-danger fw-bold mb-0">{{ $sellerJoined }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rating --}}
        <div class="card shadow-sm border-0 h-100 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">@lang('messages.prod_rating')</h5>

                @php
                    $rating = $product->rating ?? 0;
                    $percentage = ($rating / 5) * 100;
                @endphp

                {{-- Rating Header --}}
                <div class="mb-3 bg-light p-4 rounded border d-flex align-items-center gap-5 justify-content-between">
                    <div class="text-center">
                        <h2 class="text-danger fw-bold mb-0">{{ number_format($product->rating,1) }} <span class="fs-6 text-muted">@lang('messages.out_of') 5</span></h2>
                    </div>
                    <div class="star-rating">
                        <div class="stars-filled" style="width: {{ $percentage }}%">
                            ★★★★★
                        </div>
                        <div class="stars-empty">
                            ★★★★★
                        </div>
                    </div>
                </div>

                @if ($ownedReview)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="me-2 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold fs-5"
                                style="width: 40px; height: 40px">
                                {{ substr($user->username ?? $user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="margin-bottom: -2px;">{{ $ownedReview->user->username ?? $ownedReview->user->name ?? 'User' }}</div>
                                <div class="d-flex align-items-center gap-1" style="margin-top: -3px;">
                                    <div class="d-inline text-muted" style="font-size:14px">{{ number_format($ownedReview->rating,1) }}</div>
                                    <div class="star-rating" style="font-size: 20px">
                                        <div class="stars-filled" style="width: {{ $ownedReview->rating * 20 }}%">
                                            ★★★★★
                                        </div>
                                        <div class="stars-empty">
                                            ★★★★★
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted ms-auto">{{ $ownedReview->updated_at->format('d-m-Y') }}</small>
                        </div>
                        <p class="text-secondary mt-2 mb-0">{{ $ownedReview->review }}</p>
                        <button type="button" class="btn btn-outline-danger btn-sm px-4 py-2 mt-3" data-bs-toggle="modal" data-bs-target="#deleteReviewModal" data-review-id="{{ $ownedReview->id }}">
                            @lang('messages.delete') @lang('messages.review')
                        </button>
                    </div>
                @endif
                
                <form action="{{ route('rating.store', $product->id) }}" method="POST">
                    @csrf
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-2 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold fs-5"
                            style="width: 40px; height: 40px">
                            {{ substr($user->username ?? $user->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="fw-bold text-dark me-2">
                            {{ $user->username ?? $user->name ?? 'User' }}
                        </div>
                        <div class="rating-input">
                            @for ($i = 5; $i >= 1; $i--)
                                <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}">
                                <label for="star{{ $i }}">★</label>
                            @endfor
                        </div>
                    </div>
                    <textarea name="review" class="form-control mb-3" rows="3" placeholder="Write your review..."></textarea>
                    <button type="submit" class="btn btn-danger btn-sm px-4 py-2">
                        @lang('messages.submit') @lang('messages.review')
                    </button>
                </form>
            </div>
        </div>
    

        {{-- REVIEW & VOUCHER --}}
        <div class="row g-4">

            {{-- REVIEWS --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">@lang('messages.reviews')</h6>
                        {{-- List Review --}}
                        <div class="content-scroll">
                            @forelse($userReview as $review)
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <div class="bg-secondary rounded-circle me-2" style="width: 40px; height: 40px;"></div>
                                        <div>
                                            <div class="fw-bold text-dark" style="margin-bottom: -2px;">{{ $review->user->username ?? $review->user->name ?? 'User' }}</div>
                                            <div class="d-flex align-items-center gap-1" style="margin-top: -3px;">
                                                <div class="d-inline text-muted" style="font-size:14px">{{ number_format($review->rating,1) }}</div>
                                                <div class="star-rating" style="font-size: 20px">
                                                    <div class="stars-filled" style="width: {{ $review->rating * 20 }}%">
                                                        ★★★★★
                                                    </div>
                                                    <div class="stars-empty">
                                                        ★★★★★
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted ms-auto">{{ $review->updated_at->format('d-m-Y') }}</small>
                                    </div>
                                    <p class="text-secondary mt-2 mb-0">{{ $review->review }}</p>
                                </div>
                            @empty
                                <p class="text-muted fst-italic py-3">@lang('messages.no_rate').</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- VOUCHER --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">@lang('messages.store_voucher')</h6>
                        <div class="content-scroll">
                            @foreach ($seller->storeVouchers as $voucher)
                                <div class="p-3 rounded text-center position-relative" style="background-color: #ffe6e6; border: 1px dashed #dc3545;">
                                    <h6 class="fw-bold text-danger mb-0">{{ $voucher->title }}</h6>
                                    <small class="d-block text-danger mb-2">@lang('messages.min_spend') ${{ $voucher->min_purchase }}</small>
                                    <small class="d-block text-muted" style="font-size: 12px;">
                                        @lang('messages.valid_until') {{ $voucher->end_at->format('d M Y') }}
                                    </small>
                                </div>
                                <br>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if ($ownedReview)
        <div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3">

                    <div class="modal-header border-0">
                        <h5 class="modal-title text-danger fw-bold">Delete Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body border-0">
                        <p class="mb-0">
                            @lang('messages.review_del')
                            <br>
                            <small class="text-muted">@lang('messages.del_rev_confirm').</small>
                        </p>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            @lang('messages.cancel')
                        </button>

                        <form action="{{ route('rating.destroy', $ownedReview->id) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">
                                @lang('messages.yes_del')
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    @endif


<script>
    let lastChecked = null;

    const priceEl = document.getElementById('product-price');
    const stockEl = document.getElementById('product-stock');
    const flashInfoEl = document.getElementById('flashsale-info');
    const flashStockEl = document.getElementById('flashsale-stock');
    const flashInitialEl = document.getElementById('flashsale-initial');
    const flashsaleBeforeEl = document.getElementById('flashsale-before')

    document.querySelectorAll('.variant-radio').forEach(radio => {
        radio.addEventListener('click', function(){
            if (lastChecked === this){
                this.checked = false;
                lastChecked = null;

                priceEl.innerText = '{{ $product->display_price }}'
                stockEl.innerText = '{{ $product->stock }}'

                flashInfoEl.classList.add('d-none')
            } else {
                lastChecked = this;
            }
        })
        radio.addEventListener('change', function () {
            if (this.checked){
                const price = this.dataset.price;
                const stock = this.dataset.stock;
                const flashPrice = this.dataset.flashsalePrice;
                const flashStock = this.dataset.flashsaleStock;
                const flashInitial = this.dataset.flashsaleInitial;
                
                if(flashPrice){
                    priceEl.innerText = flashPrice;
                    stockEl.innerText = stock;

                    flashStockEl.innerText = flashStock;
                    flashInitialEl.innerText = flashInitial;
                    flashsaleBeforeEl.innerText = price;

                    flashInfoEl.classList.remove('d-none');
                } else {
                    priceEl.innerText = this.dataset.price;
                    stockEl.innerText = this.dataset.stock;

                    flashInfoEl.classList.add('d-none');
                }
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function(){
        const toggleBtn = document.getElementById('toggle-desc');
        const desc = document.getElementById('product-desc');

        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();

            desc.classList.toggle('desc-collapsed');

            this.innerText = desc.classList.contains('desc-collapsed') ? '@lang('messages.read_more')' : '@lang('messages.read_less')';
        })
    })
</script>
@endsection
