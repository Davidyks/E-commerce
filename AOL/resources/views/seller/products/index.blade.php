@extends('layout.seller.master')
@section('title', 'My Products')

@section('css')
<link rel="stylesheet" href="{{ asset('css/seller.css') }}">
@endsection

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex align-items-center mb-3">
        <h4 class="fw-bold text-danger mb-0">@lang('messages.my_prod')</h4>

        @if ($products->isNotEmpty())
            <a href="{{ route('products.create') }}"
               class="btn btn-sm btn-outline-danger ms-auto">
                + @lang('messages.add') @lang('messages.products')
            </a>
        @endif
    </div>

    {{-- Empty state --}}
    @if ($products->count() === 0)
        <div class="text-center py-5">
            <p class="fw-bold text-danger fs-5 mb-1">
                @lang('messages.dont_have_prod')
            </p>
            <p class="text-muted">
                @lang('messages.please_add')
            </p>
            <a href="{{ route('products.create') }}"
               class="btn btn-danger btn-sm mt-2">
                + @lang('messages.add') @lang('messages.products')
            </a>
        </div>
    @else

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0 product-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:45%">@lang('messages.products')</th>
                        <th>@lang('messages.price')</th>
                        <th>@lang('messages.stock')</th>
                        <th style="width:12%">@lang('messages.action')</th>
                    </tr>
                </thead>

                <tbody>
                @foreach ($products as $product)

                    {{--  PRODUCT ROW  --}}
                    <tr class="product-row">

                        {{-- Produk --}}
                        <td>
                            <div class="d-flex align-items-start">
                                <img
                                    src="{{ asset($product->product_image ?? 'asset/images/default-product.png') }}"
                                    class="product-thumb me-3"
                                >

                                <div>
                                    <div class="fw-semibold">
                                        {{ $product->name }}
                                    </div>

                                    {{-- Harga range / single --}}
                                    @if ($product->variants && $product->variants->count())
                                        <div class="small text-danger fw-semibold mt-1">
                                            Rp{{ number_format($product->variants->min('price'),0,',','.') }}
                                            -
                                            Rp{{ number_format($product->variants->max('price'),0,',','.') }}
                                        </div>
                                    @else
                                        <div class="small text-danger fw-semibold mt-1">
                                            Rp{{ number_format($product->price ?? 0,0,',','.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Harga (kosong jika ada variant) --}}
                        <td>
                            @unless($product->variants && $product->variants->count())
                                Rp{{ number_format($product->price ?? 0,0,',','.') }}
                            @endunless
                        </td>

                        {{-- Stok Total --}}
                        <td class="fw-semibold">
                            @if ($product->variants && $product->variants->count())
                                {{ $product->variants->sum('stock') }}
                            @else
                                {{ $product->stock ?? 0 }}
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td>
                            <a href="{{ route('products.edit', $product->id) }}"
                                class="text-primary text-decoration-none d-block ">
                                @lang('messages.edit')
                            </a>
                            <form action="{{ route('products.destroy', $product->id) }}"
                                method="POST"
                                class="delete-form d-inline">
                                @csrf
                                @method('DELETE')

                                <button type="button"
                                        class="text-danger border-0 bg-transparent p-0 delete-btn">
                                    @lang('messages.delete')
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{--  VARIANT ROWS  --}}
                    @if ($product->variants && $product->variants->count())
                        @foreach ($product->variants as $variant)
                            <tr class="variant-row">

                                {{-- Produk / Variant --}}
                                <td>
                                    <div class="d-flex align-items-center ps-5">
                                        <img
                                            src="{{ asset($variant->image) ?? asset($product->product_image) }}"
                                            class="variant-thumb me-3"
                                        >

                                        <div class="small text-muted">
                                            {{ $variant->variant_name }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Harga Variant --}}
                                <td class="small">
                                    Rp{{ number_format($variant->price ?? 0,0,',','.') }}
                                </td>

                                {{-- Stok Variant --}}
                                <td class="small">
                                    {{ $variant->stock ?? 0 }}
                                </td>

                                {{-- Aksi kosong --}}
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-end m-3 gap-5">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('.delete-form');

            Swal.fire({
                title: '@lang('messages.del_prod')?',
                text: '@lang('messages.del_confirm').',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '@lang('messages.yes_del')',
                cancelButtonText: '@lang('messages.cancel')'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush

@endsection
