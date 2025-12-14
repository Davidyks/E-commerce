@extends('layout.seller.master')
@section('title', 'My Products')

@section('css')
<link rel="stylesheet" href="{{ asset('css/seller-products.css') }}">
@endsection

@section('content')

<div class="container-fluid">

{{-- Header --}}
<div class="d-flex align-items-center mb-3">
    <h4 class="fw-bold text-danger mb-0">My Products</h4>
    @if ($products->isNotEmpty())
        <a href="{{ route('products.create') }}" class="btn btn-sm btn-outline-danger ms-auto">
            + Add Product
        </a>
    @endif
</div>

@if ($products->isEmpty())
    <div class="text-center py-5">
        <p class="fw-bold text-danger fs-5 mb-1">Anda belum memiliki produk</p>
        <p class="text-muted">Silakan tambahkan produk terlebih dahulu</p>
        <a href="{{ route('products.create') }}" class="btn btn-danger btn-sm mt-2">
            + Tambah Produk
        </a>
    </div>
@else

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0 product-table">
            <thead class="table-light">
                <tr>
                    <th style="width:45%">Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th style="width:12%">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
                <tr>
                    {{-- Produk --}}
                    <td>
                        {{-- Product main --}}
                        <div class="d-flex mb-2">
                            <img src="{{ $product->product_image ?? asset('asset/images/sesudah_login/shirt.jpg') }}"
                                 class="product-thumb me-3">
                            <div>
                                <div class="fw-semibold">{{ $product->name }}</div>
                            </div>
                        </div>

                        @if ($product->variants && $product->variants->count())
                            <div class="variant-list ms-1">
                                @foreach ($product->variants as $variant)
                                    <div class="d-flex align-items-center variant-item">
                                        <img src="{{ $variant->image ?? $product->product_image }}"
                                             class="variant-thumb me-2">
                                        <span class="small text-muted">
                                            {{ $variant->variant_name }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    {{-- Harga --}}
                    <td class="fw-semibold text-danger align-top">
                        @if ($product->variants && $product->variants->count())
                            Rp{{ number_format($product->variants->min('price'),0,',','.') }}
                            -
                            Rp{{ number_format($product->variants->max('price'),0,',','.') }}
                        @else
                            Rp{{ number_format($product->price ?? 0,0,',','.') }}
                        @endif
                    </td>

                    {{-- Stok --}}
                    <td class="align-top">
                        @if ($product->variants && $product->variants->count())
                            {{ $product->variants->sum('stock') }}
                        @else
                            {{ $product->stock ?? 0 }}
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="align-top">
                        <a href="{{ route('products.detail', $product->id) }}" class="text-primary d-block mb-1">
                            Detail
                        </a>
                        <a href="{{ route('products.edit', $product->id) }}" class="text-primary d-block">
                            Edit
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif

</div>
@endsection
