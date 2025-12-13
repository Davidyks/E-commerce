@extends('layout.sesudah_login.master')

@section('content')
<div class="container py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <h3 class="text-danger fw-bold mb-4">Cart</h3>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div class="form-check d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="selectAll" style="width: 1.2em; height: 1.2em;">
                        <label class="form-check-label fw-bold" for="selectAll">
                            Choose All <span class="text-muted fw-normal">({{ $totalItems ?? 0 }})</span>
                        </label>
                    </div>
                    <a href="#" class="text-danger fw-bold text-decoration-none">Delete</a>
                </div>
            </div>

            @forelse($groupedItems as $sellerId => $items)
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <input class="form-check-input me-3" type="checkbox" style="width: 1.2em; height: 1.2em;">
                            <span class="fw-bold fs-5">
                                {{ $items->first()->seller->store_name ?? 'Nama Toko' }}
                            </span>
                        </div>

                        @foreach($items as $item)
                        <div class="row mb-4 align-items-center">
                            <div class="col-1">
                                <input class="form-check-input" type="checkbox" style="width: 1.2em; height: 1.2em;">
                            </div>
                            <div class="col-2">
                                <img src="{{ asset('storage/' . $item->product->product_image) }}" 
                                     class="img-fluid rounded" 
                                     alt="Produk"
                                     style="width: 100%; aspect-ratio: 1/1; object-fit: contain; border: 1px solid #eee;">
                            </div>
                            <div class="col-5">
                                <h6 class="mb-1 fw-normal">{{ $item->product->name }}</h6>
                            </div>
                            <div class="col-4 text-end">
                                <div class="text-danger fw-bold mb-1 fs-5">
                                    Rp. {{ number_format($item->price, 0, ',', '.') }}
                                </div>
                                <div class="text-muted text-decoration-line-through small mb-2">
                                    Rp. {{ number_format($item->price * 1.2, 0, ',', '.') }}
                                </div>
                                <div class="d-flex justify-content-end align-items-center gap-3">
                                    <div class="input-group input-group-sm" style="width: 100px; border: 1px solid #dee2e6; border-radius: 5px;">
                                        <button class="btn btn-white text-danger fw-bold px-2" type="button">-</button>
                                        <input type="text" class="form-control text-center border-0 bg-transparent p-1" value="{{ $item->quantity }}">
                                        <button class="btn btn-white text-danger fw-bold px-2" type="button">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 8px;">
                    <div class="card-body">
                        <h4 class="fw-bold mt-3">Wow, your cart is empty!</h4>
                        <p class="text-muted mb-4">Come on, fill it with your dream items.</p>
                        <a href="{{ url('/home') }}" class="btn btn-danger px-5 py-2 fw-bold" style="border-radius: 8px;">
                            Start Shopping
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-danger mb-4">Shopping Summary</h5>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">VOUCHER CODE</label>
                        
                        @if(isset($voucherCode) && $voucherCode)
                            <div class="d-flex justify-content-between align-items-center p-2 border border-success rounded bg-light">
                                <span class="text-success fw-bold">
                                    <i class="bi bi-ticket-fill me-1"></i> {{ $voucherCode }}
                                </span>
                                <form action="{{ route('cart.voucher.remove') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger fw-bold p-0" title="Hapus Voucher">
                                        <i class="bi bi-x-circle-fill fs-5"></i>
                                    </button>
                                </form>
                            </div>
                            <small class="text-success fst-italic">Voucher applied successfully!</small>

                        @else
                            <button type="button" class="btn d-flex justify-content-between align-items-center px-3 py-2 w-100" 
                                    style="background-color: #fcebeb; color: #333; border: 1px solid #f5c6cb; border-radius: 8px;"
                                    data-bs-toggle="modal" data-bs-target="#voucherModal">
                                <span class="d-flex align-items-center">
                                    <i class="bi bi-ticket-perforated-fill text-danger me-2"></i> 
                                    Use coupons
                                </span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </button>
                        @endif
                    </div>

                    <hr class="text-muted">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">Rp. {{ number_format($subtotal ?? 0, 0, ',', '.') }}</span>
                    </div>

                    @if(isset($discountAmount) && $discountAmount > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-success">Discount</span>
                        <span class="fw-bold text-success">- Rp. {{ number_format($discountAmount, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <hr class="text-muted my-3">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold text-danger fs-4">Rp. {{ number_format($finalTotal ?? 0, 0, ',', '.') }}</span>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-danger fw-bold py-2 fs-5" style="border-radius: 8px;">
                            Buy Now
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="voucherModalLabel">Available Vouchers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light">
        
        @if(isset($availableVouchers) && count($availableVouchers) > 0)
            @foreach($availableVouchers as $voucher)
                <div class="card mb-2 border-0 shadow-sm hover-shadow">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-danger mb-1">{{ $voucher->code }}</h6>
                            <small class="d-block text-dark fw-bold">{{ $voucher->title }}</small>
                            <small class="text-muted" style="font-size: 0.8rem;">
                                Min. Purchase: Rp {{ number_format($voucher->min_purchase, 0, ',', '.') }}
                            </small>
                        </div>

                        <form action="{{ route('cart.voucher.apply') }}" method="POST">
                            @csrf
                            <input type="hidden" name="code" value="{{ $voucher->code }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">Apply</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-5">
                <i class="bi bi-ticket-perforated text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No vouchers available at the moment.</p>
            </div>
        @endif

      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endsection