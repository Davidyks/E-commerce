@extends('layout.seller.master')
@section('title', 'Add Product')

@section('content')
<div class="container">

{{-- ERROR --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
@csrf

{{--  INFO PRODUK  --}}
<div class="card mb-3">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Informasi Produk</h5>

        <div class="mb-3">
            <label>Nama Produk</label>
            <input type="text" name="name"
                   class="form-control"
                   value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="description"
                      class="form-control"
                      rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label>Kategori</label>
            <select name="category_id" class="form-control">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col">
                <label>Minimal Order</label>
                <input type="number"
                       name="min_order_qty"
                       value="{{ old('min_order_qty', 1) }}"
                       class="form-control">
            </div>
            <div class="col">
                <label>Estimasi Pengiriman (Hari)</label>
                <input type="number"
                       name="delivery_estimate_days"
                       value="{{ old('delivery_estimate_days') }}"
                       class="form-control">
            </div>
        </div>

        <div class="mt-3">
            <label>Gambar Produk</label>
            <input type="file" name="product_image" class="form-control">
        </div>
    </div>
</div>

{{--  PILIH VARIANT  --}}
<div class="card mb-3">
    <div class="card-body">
        <label class="fw-bold">Apakah produk memiliki varian?</label>
        <div class="mt-2">
            <input type="radio" name="has_variant" value="0"
                {{ old('has_variant', '0') == '0' ? 'checked' : '' }}> Tidak
            <input type="radio" name="has_variant" value="1"
                {{ old('has_variant') == '1' ? 'checked' : '' }} class="ms-3"> Ya
        </div>
    </div>
</div>

{{--  TANPA VARIANT  --}}
<div id="no-variant-section">
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <label>Harga</label>
                    <input type="number"
                           name="price"
                           value="{{ old('price') }}"
                           class="form-control">
                </div>
                <div class="col">
                    <label>Stok</label>
                    <input type="number"
                           name="stock"
                           value="{{ old('stock') }}"
                           class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>

{{--  VARIANT  --}}
<div id="variant-section" style="display:none;">
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Varian Produk</h6>

            <div id="variant-wrapper">
                @if (old('variants'))
                    @foreach (old('variants') as $i => $variant)
                        <div class="border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col">
                                    <label>Nama Varian</label>
                                    <input type="text"
                                           name="variants[{{ $i }}][variant_name]"
                                           value="{{ $variant['variant_name'] ?? '' }}"
                                           class="form-control">
                                </div>
                                <div class="col">
                                    <label>Harga</label>
                                    <input type="number"
                                           name="variants[{{ $i }}][price]"
                                           value="{{ $variant['price'] ?? '' }}"
                                           class="form-control">
                                </div>
                                <div class="col">
                                    <label>Stok</label>
                                    <input type="number"
                                           name="variants[{{ $i }}][stock]"
                                           value="{{ $variant['stock'] ?? '' }}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="mt-2">
                                <label>Gambar Varian</label>
                                <input type="file"
                                       name="variants[{{ $i }}][image]"
                                       class="form-control">
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <button type="button"
                    id="add-variant"
                    class="btn btn-sm btn-outline-primary">
                + Tambah Varian
            </button>
        </div>
    </div>
</div>

{{--  BUTTON  --}}
<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">
        Cancel
    </a>
    <button type="submit" class="btn btn-danger">
        Upload
    </button>
</div>

</form>
</div>

{{--  SCRIPT  --}}
<script>
const variantSection = document.getElementById('variant-section');
const noVariantSection = document.getElementById('no-variant-section');
const wrapper = document.getElementById('variant-wrapper');

document.querySelectorAll('[name="has_variant"]').forEach(el => {
    el.addEventListener('change', function () {
        if (this.value === '1') {
            variantSection.style.display = 'block';
            noVariantSection.style.display = 'none';
            if (wrapper.children.length === 0) addVariant();
        } else {
            variantSection.style.display = 'none';
            noVariantSection.style.display = 'block';
        }
    });
});

function addVariant() {
    const index = wrapper.children.length;
    wrapper.insertAdjacentHTML('beforeend', `
        <div class="border rounded p-3 mb-3">
            <div class="row">
                <div class="col">
                    <label>Nama Varian</label>
                    <input type="text" name="variants[${index}][variant_name]" class="form-control">
                </div>
                <div class="col">
                    <label>Harga</label>
                    <input type="number" name="variants[${index}][price]" class="form-control">
                </div>
                <div class="col">
                    <label>Stok</label>
                    <input type="number" name="variants[${index}][stock]" class="form-control">
                </div>
            </div>
            <div class="mt-2">
                <label>Gambar Varian</label>
                <input type="file" name="variants[${index}][image]" class="form-control">
            </div>
        </div>
    `);
}

document.addEventListener('DOMContentLoaded', function () {
    if ("{{ old('has_variant') }}" === '1') {
        variantSection.style.display = 'block';
        noVariantSection.style.display = 'none';
    }
});

document.getElementById('add-variant').addEventListener('click', addVariant);
</script>
@endsection
