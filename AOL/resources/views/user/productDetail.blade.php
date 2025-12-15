@extends('layout.sesudah_login.master')
@section('title', 'Products')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="container py-5">

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <strong>Success!</strong> {{ session('success') }}
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
                            <img src="{{ asset('asset/images/product/' . $product->product_image) }}"
                                alt="{{ $product->name }}" class="img-fluid w-100 object-fit-cover"
                                style="aspect-ratio: 1/1;">
                        </div>
                    </div>

                    {{-- Detail --}}
                    <div class="col-md-7">
                        <h3 class="fw-bold text-dark mb-2">{{ $product->name }}</h3>

                        {{-- Rating & Sold --}}
                        <div class="d-flex align-items-center mb-3 small">
                            <span class="text-warning me-1">
                                {{ $product->rating_average }} <i class="fas fa-star"></i>
                            </span>
                            <span class="text-muted mx-2">|</span>
                            <span class="text-muted text-decoration-underline">{{ $product->sold_count }} Sold</span>
                        </div>

                        {{-- Harga --}}
                        <h2 class="text-danger fw-bold mb-4">
                            Rp {{ $product->display_price }}
                        </h2>

                        {{-- FORM --}}
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            {{-- Varian --}}
                            @if ($product->variants->count() > 0)
                                <div class="mb-3">
                                    <label class="fw-bold mb-2 text-secondary">Variant</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($product->variants as $variant)
                                            <input type="radio" class="btn-check" name="variant_id"
                                                id="var_{{ $variant->id }}" value="{{ $variant->id }}" required>
                                            <label class="btn btn-outline-secondary px-4 py-2 rounded-pill"
                                                for="var_{{ $variant->id }}">
                                                {{ $variant->variant_name }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('variant_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <hr class="text-secondary opacity-25 my-4">

                            {{-- Detail --}}
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark mb-2">Detail Product</h6>
                                <hr class="text-secondary opacity-25 my-4">
                                <ul class="list-unstyled text-secondary small mb-2">
                                    <li>• Condition: New</li>
                                    <li>• Min. Order: {{ $product->min_order_qty }} Piece</li>
                                </ul>
                                <p class="text-secondary small mb-0">
                                    {{ $product->description }}
                                    <a href="#" class="text-danger text-decoration-none fw-bold">Read More</a>
                                </p>
                            </div>

                            <hr class="text-secondary opacity-25 my-4">
                            {{-- Section 3: Delivery Info --}}
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark mb-2">Delivery</h6>
                                <hr class="text-secondary opacity-25 my-4">
                                <div class="d-flex align-items-start gap-2 text-secondary small">
                                    <i class="fas fa-truck mt-1 fs-5"></i>
                                    <div>
                                        <span class="fw-bold text-dark">Estimate
                                            {{ $product->delivery_estimate_days ?? 2 }} Days</span><br>
                                        Get a voucher up to IDR 10.000 if your order is late.
                                    </div>
                                </div>
                            </div>

                            {{-- GARIS PEMBATAS 3 (Di atas Tombol / Di bawah Delivery) --}}
                            <hr class="text-secondary opacity-25 my-4">

                            {{-- Action Buttons & Share --}}
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex gap-3 align-items-center flex-grow-1 me-4">
                                    {{-- Quantity --}}
                                    <div style="width: 70px;">
                                        {{-- <label class="form-label fw-bold text-secondary small mb-0">Qty</label> --}}
                                        <input type="number" name="quantity"
                                            value="{{ old('quantity', $product->min_order_qty) }}"
                                            min="{{ $product->min_order_qty }}" class="form-control text-center fw-bold"
                                            placeholder="1">
                                    </div>

                                    <button type="submit" name="action" value="add_to_cart"
                                        class="btn btn-light text-danger border-danger px-4 py-2 fw-bold"
                                        style="background-color: #ffe6e6;">
                                        <i class="fas fa-cart-plus me-2"></i> Add To Cart
                                    </button>
                                    <button type="submit" name="action" value="buy_now"
                                        class="btn btn-danger px-4 py-2 fw-bold">
                                        Buy Now
                                    </button>
                                </div>

                                {{-- Kanan: Share & Wishlist --}}
                                <div class="d-flex gap-3 text-danger small fw-bold text-nowrap">
                                    <span style="cursor: pointer;">
                                        <i class="fas fa-share-alt me-1"></i> Share
                                    </span>
                                    <span style="cursor: pointer;">
                                        <i class="far fa-heart me-1"></i> Wishlist
                                    </span>
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
                                <button class="btn btn-outline-danger btn-sm px-3"><i class="far fa-comment-dots"></i>
                                    Chat</button>
                                <button class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-store"></i>
                                    Visit</button>
                            </div>
                        </div>
                    </div>

                    {{-- Stat Toko --}}
                    <div class="col-md-8">
                        <div class="d-flex justify-content-around text-center">
                            <div>
                                <span class="text-secondary small">Products</span>
                                <h5 class="text-danger fw-bold mb-0">180</h5>
                            </div>
                            <div>
                                <span class="text-secondary small">Rating</span>
                                <h5 class="text-danger fw-bold mb-0">5.0</h5>
                            </div>
                            <div>
                                <span class="text-secondary small">Joined</span>
                                <h5 class="text-danger fw-bold mb-0">9 Months</h5>
                            </div>
                            <div>
                                <span class="text-secondary small">Response</span>
                                <h5 class="text-danger fw-bold mb-0">100%</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- REVIEW & VOUCHER --}}
        <div class="row g-4">

            {{-- REVIEWS --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Product Rating</h5>

                        {{-- Rating Header --}}
                        <div class="bg-light p-4 rounded mb-4 border d-flex align-items-center gap-5">
                            <div class="text-center">
                                <h2 class="text-danger fw-bold mb-0">5.0 <span class="fs-6 text-muted">out of 5</span>
                                </h2>
                                <div class="text-warning"><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-outline-secondary px-4 active">All</button>
                                <button class="btn btn-outline-secondary px-4">5 Star</button>
                                <button class="btn btn-outline-secondary px-4">4 Star</button>
                                <button class="btn btn-outline-secondary px-4">With Media</button>
                            </div>
                        </div>

                        {{-- List Review --}}
                        @forelse($product->ratings as $rating)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="bg-secondary rounded-circle me-2" style="width: 30px; height: 30px;">
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark">{{ $rating->user->name ?? 'User' }}</span>
                                        <div class="text-warning x-small" style="font-size: 0.75rem;">
                                            @for ($i = 0; $i < $rating->rating; $i++)
                                                <i class="fas fa-star"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <small class="text-muted ms-auto">{{ $rating->created_at->format('d-m-Y') }}</small>
                                </div>
                                <p class="text-secondary mt-2 mb-0">{{ $rating->review }}</p>
                            </div>
                        @empty
                            <p class="text-muted fst-italic py-3">No ratings yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- VOUCHER --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Store Voucher</h6>

                        <div class="p-3 rounded text-center position-relative"
                            style="background-color: #ffe6e6; border: 1px dashed #dc3545;">
                            <h6 class="fw-bold text-danger mb-1">Discount 12%</h6>
                            <small class="d-block text-danger mb-3">Min. Spend Rp1.000.000</small>
                            <button class="btn btn-danger btn-sm w-100 fw-bold">Claim</button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
