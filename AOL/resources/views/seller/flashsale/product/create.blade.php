@extends('layout.seller.master')
@section('title', 'Add Flashsale')
@section('content')

<style>
    .card-body span{
        font-size: 0.95rem;
    }

    @media (max-width:767px){
        .card-body span{
            font-size: 0.8rem;
        }

        header {
            pointer-events: none;
        }

        header * {
            pointer-events: auto;
        }
    }
</style>

<div class="container px-4">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('seller.flashsale.product.store', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="fw-bold mb-3">@lang('messages.prod_info')</h5>
                <h6 class="mb-0 fw-bold text-danger">{{ $product->name }}</h6>
                <div class="d-flex flex-column">
                    <span>@lang('messages.stock'): {{ $product->stock }}</span>
                    <span>@lang('messages.price'): {{ $product->price }}</span>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="fw-bold mb-3">@lang('messages.add_flashsale')</h5>

                <div class="row g-3">
                    <div class="col-md-6 col-12">
                        <label for="start_at" class="form-label fw-semibold">
                            @lang('messages.start')
                        </label>
                        <input type="text" id="start_at" name="start_at" class="form-control" value="{{ now()->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6 col-12">
                        <label for="end_at" class="form-label fw-semibold">
                            @lang('messages.end')
                        </label>
                        <input type="text" id="end_at" name="end_at" class="form-control" value="{{ now()->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6 col-12">
                        <label for="price" class="form-label fw-semibold">
                            @lang('messages.price')
                        </label>
                        <input type="number" step="0.01" name="price" class="form-control" min="1" max="{{ $product->price }}" value="{{ $product->price }}">
                    </div>
                    <div class="col-md-6 col-12">
                        <label for="stock" class="form-label fw-semibold">
                            @lang('messages.stock')
                        </label>
                        <input type="number" name="stock" class="form-control" min="1" max="{{  $product->stock }}" value="{{ $product->stock }}">
                    </div>
                </div>
            </div>
        </div>

        {{--  BUTTON  --}}
        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">
                @lang('messages.cancel')
            </a>
            <button type="submit" class="btn btn-danger">
                @lang('messages.add')
            </button>
        </div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr("#start_at", {
            dateFormat: "Y-m-d",
            minDate: "today",
            disableMobile: true
        });

        flatpickr("#end_at", {
            dateFormat: "Y-m-d",
            minDate: "today",
            disableMobile: true
        });
    });
</script>
@endsection
