@extends('layout.seller.master')
@section('title', 'Edit Product')

@section('content')
<div class="container">

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('products.update', $product->id) }}"
      method="POST"
      enctype="multipart/form-data">
@csrf
@method('PUT')

{{-- INFO PRODUK --}}
<div class="card mb-3">
    <div class="card-body">
        <h5 class="fw-bold mb-3">@lang('messages.prod_info')</h5>

        <div class="mb-3">
            <label>@lang('messages.prod_name')</label>
            <input type="text"
                   name="name"
                   value="{{ $product->name }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>@lang('messages.description')</label>
            <textarea name="description"
                      class="form-control"
                      rows="3">{{ $product->description }}</textarea>
        </div>

        <div class="mb-3">
            <label>@lang('messages.category')</label>
            <select name="category_id" class="form-control">
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col">
                <label>@lang('messages.min_order')</label>
                <input type="number"
                       name="min_order_qty"
                       value="{{ $product->min_order_qty }}"
                       class="form-control">
            </div>
            <div class="col">
                <label>@lang('messages.est_day')</label>
                <input type="number"
                       name="delivery_estimate_days"
                       value="{{ $product->delivery_estimate_days }}"
                       class="form-control">
            </div>
        </div>

        <div class="mt-3">
            <label>@lang('messages.prod_pict')</label>
            <input type="file" name="product_image" class="form-control">
            @if ($product->product_image)
                <img src="{{ asset($product->product_image) }}"
                     width="120"
                     class="mt-2">
            @endif
        </div>
    </div>
</div>

{{-- JIKA TIDAK ADA VARIANT--}}
@if ($product->variants->count() === 0)

<div class="card mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">@lang('messages.price') & @lang('messages.stock')</h6>
        <div class="row">
            <div class="col">
                <label>@lang('messages.price')</label>
                <input type="number"
                       name="price"
                       value="{{ $product->price }}"
                       class="form-control">
            </div>
            <div class="col">
                <label>@lang('messages.stock')</label>
                <input type="number"
                       name="stock"
                       value="{{ $product->stock }}"
                       class="form-control">
            </div>
        </div>
    </div>
</div>

@endif

{{--JIKA ADA VARIANT--}}
@if ($product->variants->count() > 0)

<div class="card mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">@lang('messages.prod_var')</h6>

        <div id="variant-wrapper">

            @foreach ($product->variants as $i => $variant)
            <div class="border rounded p-3 mb-3 variant-item">
                <input type="hidden"
                       name="variants[{{ $i }}][id]"
                       value="{{ $variant->id }}">

                <div class="row">
                    <div class="col">
                        <label>@lang('messages.var_name')</label>
                        <input type="text"
                               name="variants[{{ $i }}][variant_name]"
                               value="{{ $variant->variant_name }}"
                               class="form-control">
                    </div>
                    <div class="col">
                        <label>@lang('messages.price')</label>
                        <input type="number"
                               name="variants[{{ $i }}][price]"
                               value="{{ $variant->price }}"
                               class="form-control">
                    </div>
                    <div class="col">
                        <label>@lang('messages.stock')</label>
                        <input type="number"
                               name="variants[{{ $i }}][stock]"
                               value="{{ $variant->stock }}"
                               class="form-control">
                    </div>
                </div>

                <div class="mt-2">
                    <label>@lang('messages.var_pict')</label>
                    <input type="file"
                           name="variants[{{ $i }}][image]"
                           class="form-control">
                    @if ($variant->image)
                        <img src="{{ asset($variant->image) }}"
                             width="100"
                             class="mt-2">
                    @endif
                </div>

                <button type="button"
                        class="btn btn-sm btn-outline-danger mt-2 remove-variant">
                    @lang('messages.del_var')
                </button>
            </div>
            @endforeach

        </div>

        <button type="button"
                id="add-variant"
                class="btn btn-sm btn-outline-primary">
            + @lang('messages.add_var')
        </button>
    </div>
</div>

@endif

{{-- BUTTON --}}
<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('products.index') }}"
       class="btn btn-secondary me-2">
        @lang('messages.cancel')
    </a>
    <button type="submit"
            class="btn btn-danger">
        @lang('messages.update')
    </button>
</div>

</form>
</div>

{{-- SCRIPT --}}
<script>
const wrapper = document.getElementById('variant-wrapper');

if (wrapper) {
    document.getElementById('add-variant').addEventListener('click', () => {
        const index = wrapper.children.length;
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="border rounded p-3 mb-3 variant-item">
                <div class="row">
                    <div class="col">
                        <label>@lang('messages.var_name')</label>
                        <input type="text"
                               name="variants[${index}][variant_name]"
                               class="form-control">
                    </div>
                    <div class="col">
                        <label>@lang('messages.price')</label>
                        <input type="number"
                               name="variants[${index}][price]"
                               class="form-control">
                    </div>
                    <div class="col">
                        <label>@lang('messages.stock')</label>
                        <input type="number"
                               name="variants[${index}][stock]"
                               class="form-control">
                    </div>
                </div>
                <div class="mt-2">
                    <label>@lang('messages.var_pict')</label>
                    <input type="file"
                           name="variants[${index}][image]"
                           class="form-control">
                </div>
                <button type="button"
                        class="btn btn-sm btn-outline-danger mt-2 remove-variant">
                    @lang('messages.del_var')
                </button>
            </div>
        `);
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variant')) {
            e.target.closest('.variant-item').remove();
        }
    });
}
</script>
@endsection
