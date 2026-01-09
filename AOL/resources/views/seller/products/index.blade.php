@extends('layout.seller.master')
@section('title', 'My Products')

@section('css')
<link rel="stylesheet" href="{{ asset('css/seller.css') }}">
@endsection

@section('content')
<div class="container-fluid px-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissable fade show mb-3 d-flex justify-content-between align-items-center" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                                            ${{ number_format($product->variants->min('price'),2) }}
                                            -
                                            ${{ number_format($product->variants->max('price'),2) }}
                                        </div>
                                    @else
                                        <div class="small text-danger fw-semibold mt-1">
                                            ${{ number_format($product->price ?? 0,2) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Harga (kosong jika ada variant) --}}
                        <td>
                            @unless($product->variants && $product->variants->count())
                                ${{ number_format($product->price ??  0, 2) }}
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
                            <div class="d-flex flex-column flex-md-row gap-2">
                                <a href="{{ route('products.edit', $product->id) }}"
                                    class="btn btn-sm btn-primary text-white">
                                    @lang('messages.edit')
                                </a>
                                <form action="{{ route('products.destroy', $product->id) }}"
                                    method="POST"
                                    class="delete-form d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="button"
                                            class="btn btn-sm btn-danger delete-btn text-white">
                                        @lang('messages.delete')
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    @php
                        $flashsaleProduct = $flashsales->firstWhere('product_id', $product->id);
                    @endphp

                    @if ($flashsaleProduct)
                        <tr class="flashsale-row">
                            <td>
                                <div class="d-block align-items-start">
                                    <div class="text-danger fw-bold"><small>Flashsale</small></div>
                                    <div class="small">
                                        <small>@lang('messages.start'): {{ \Carbon\Carbon::parse($flashsaleProduct->start_time)->format('d M Y, H:i') }}</small>
                                        <br>
                                        <small>@lang('messages.end'): {{ \Carbon\Carbon::parse($flashsaleProduct->end_time)->format('d M Y, H:i') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-semibold">
                                <small>${{ number_format($flashsaleProduct->flash_price, 2) }}</small>
                            </td>
                            <td class="fw-semibold">
                                <small>{{ $flashsaleProduct->flash_stock }}/{{ $flashsaleProduct->initial_stock }}</small>
                            </td>
                            <td>
                                <div class="d-flex flex-column flex-md-row gap-2">
                                    <a href="{{ route('seller.flashsale.edit', $flashsaleProduct) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <small>@lang('messages.edit')</small>
                                    </a>
                                    <form action="{{ route('seller.flashsale.destroy', $flashsaleProduct) }}"
                                        method="POST"
                                        class="delete-flashsale-form d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-btn flashsale-delete">
                                            <small>@lang('messages.delete')</small>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>                        
                    @else
                        @if ($product->variants->count() === 0)
                            <tr class="flashsale-row">
                                <td>
                                    <a href="{{ route('seller.flashsale.product.create', $product) }}" class="text-danger text-decoration-none fw-bold small">+ @lang('messages.add_flashsale')</a>
                                </td>
                            </tr>
                        @endif
                    @endif

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
                                <td>
                                    <small>${{ number_format($variant->price ?? 0,2) }}</small>
                                </td>

                                {{-- Stok Variant --}}
                                <td>
                                    <small>{{ $variant->stock ?? 0 }}</small>
                                </td>

                                {{-- Aksi kosong --}}
                                <td></td>
                            </tr>

                            @php
                                $flashsaleVariant = $flashsales->firstWhere('product_variant_id', $variant->id)
                            @endphp
                            
                            @if ($flashsaleVariant)
                                <tr class="flashsale-row">
                                    <td>
                                        <div class="d-block align-items-start ps-5">
                                            <div class="text-danger fw-bold"><small>Flashsale</small></div>
                                            <div class="small">
                                                <small>@lang('messages.start'): {{ \Carbon\Carbon::parse($flashsaleVariant->start_time)->format('d M Y, H:i') }}</small>
                                                <br>
                                                <small>@lang('messages.end'): {{ \Carbon\Carbon::parse($flashsaleVariant->end_time)->format('d M Y, H:i') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold">
                                        <small>${{ number_format($flashsaleVariant->flash_price, 2) }}</small>
                                    </td>
                                    <td class="fw-semibold">
                                        <small>{{ $flashsaleVariant->flash_stock }}/{{ $flashsaleVariant->initial_stock }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column flex-md-row gap-2">
                                            <a href="{{ route('seller.flashsale.edit', $flashsaleVariant) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <small>@lang('messages.edit')</small>
                                            </a>
                                            <form action="{{ route('seller.flashsale.destroy', $flashsaleVariant) }}"
                                                method="POST"
                                                class="delete-flashsale-form d-inline">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger delete-btn flashsale-delete">
                                                    <small>@lang('messages.delete')</small>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>                        
                            @else
                                <tr class="flashsale-row">
                                    <td>
                                        <a href="{{ route('seller.flashsale.variant.create', $variant) }}" class="text-danger text-decoration-none fw-bold small ps-5"><small>+ @lang('messages.add_flashsale')</small></a>
                                    </td>
                                </tr>
                            @endif
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

    document.querySelectorAll('.flashsale-delete').forEach(button=>{
        button.addEventListener('click', function(){
            const form = this.closest('.delete-flashsale-form');

            Swal.fire({
                title: '@lang('messages.del_flash')?',
                text: '@lang('messages.del_confirm_flash').',
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
        })
    })
});
</script>
@endpush

@endsection
