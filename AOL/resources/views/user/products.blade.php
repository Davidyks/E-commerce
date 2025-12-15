@extends('layout.sesudah_login.master') 
@section('title', 'Products')
@section('css', 'css/products.css')
@section('content')
<div class="container-fluid">
    <div class="mx-auto py-4 rounded shadow-sm d-flex justify-content-between" style="background: #fff; width: 95%;">
        <h4 class="fw-bold mb-0 ms-4" style="color:#e63939">
            All Products
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
            <a href="{{ $isActive ? route('products') : route('products', array_merge(request()->query(), ['category' => $c->id])) }}" class="category-item {{ $isActive ? 'active' : '' }}">
                <img src="{{ $c->category_image }}">
                <div>{{ $c->category_name }}</div>
            </a>
        @endforeach
    </div>

    <div class="category-list mx-auto sort-bar">

        @php
            $priceActive = request('sort') === 'price_asc' || request('sort') === 'price_desc';
            $priceNext = match(request('sort')){
                'price_asc' => 'price_desc',
                'price_desc' => null,
                default => 'price_asc',
            };
            $ratingActive = request('sort') === 'rating_asc' || request('sort') === 'rating_desc';
            $ratingNext = match(request('sort')){
                'rating_asc' => 'rating_desc',
                'rating_desc' => null,
                default => 'rating_asc',
            };
            $popularActive = request('sort') === 'sold_count_asc' || request('sort') === 'sold_count_desc';
            $popularNext = match(request('sort')){
                'sold_count_asc' => 'sold_count_desc',
                'sold_count_desc' => null,
                default => 'sold_count_asc',
            };
            $latestActive = request('sort') === 'latest_asc' || request('sort') === 'latest_desc';
            $latestNext = match(request('sort')){
                'latest_asc' => 'latest_desc',
                'latest_desc' => null,
                default => 'latest_asc',
            };
        @endphp

        <a href="{{ route('products', array_filter(array_merge(request()->query(), ['sort' => $priceNext]))) }}" class="sort-item {{ $priceActive ? 'active' : '' }}">
            Price
            <span>
                {{ request('sort') === 'price_asc' ? ' (Low to High)' : (request('sort') === 'price_desc' ? ' (High to Low)' : '') }}
            </span>
        </a>
        <a href="{{ route('products', array_filter(array_merge(request()->query(), ['sort'=>$ratingNext]))) }}" class="sort-item {{ $ratingActive ? 'active' : ''}}">
            Rating
            <span>
                {{ request('sort') === 'rating_asc' ? ' (Low to High)' : (request('sort') === 'rating_desc' ? ' (High to Low)' : '') }}
            </span>
        </a>
        <a href="{{ route('products', array_filter(array_merge(request()->query(), ['sort'=>$popularNext]))) }}" class="sort-item {{ $popularActive ? 'active' : ''}}">
            Most Popular
            <span>
                {{ request('sort') === 'sold_count_asc' ? ' (Low to High)' : (request('sort') === 'sold_count_desc' ? ' (High to Low)' : '') }}
            </span>
        </a>
        <a href="{{ route('products', array_filter(array_merge(request()->query(), ['sort'=>$latestNext]))) }}" class="sort-item {{ $latestActive ? 'active' : ''}}">
            Latest
            <span>
                {{ request('sort') === 'latest_asc' ? ' (Old to New)' : (request('sort') === 'latest_desc' ? ' (New to Old)' : '') }}
            </span>
        </a>
    </div>


    <div class="product-container">
        <div class="product-header">
            <span class="product-title d-flex align-items-center">
                Products
                @if(request('q'))
                    <span class="text-muted ms-2" style="font-size: 16px;">
                        | Showing results for "<b>{{ request('q') }}</b>"
                    </span>
                @endif
            </span>
        </div>
       
        @if ($products->isNotEmpty())
            <div class="product-items">
                @foreach ($products as $p)
                    <div class="product-card">
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
                                    @if(request('sort') === 'price_desc')
                                        ${{ $p->max_price }} - ${{ $p->min_price }}
                                    @else
                                        ${{ $p->min_price }} - ${{ $p->max_price }}
                                    @endif
                                @endif
                            </p>
                            <div class="align-items-center d-flex justify-content-between">
                                <p class="before-discount text-muted text-decoration-none">{{ $p->sold_count }} Sold</p>
                                <div class="product-rate">
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
@endsection
