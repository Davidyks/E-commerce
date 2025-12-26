@extends('layout.sesudah_login.master')

@section('title','Cart')
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger fw-bold mb-0">Cart</h3>
        <a href="{{ route('home') }}" class="fs-5 fw-semibold text-decoration-none" style="color: #e63939;">
            Back
        </a>
    </div>

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
                                <img src="{{ $item->product->product_image ?? asset('asset/images/sesudah_login/shirt.jpg') }}" 
                                    class="rounded" 
                                    style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #eee;"
                                    onerror="this.onerror=null;this.src='{{ asset('asset/images/sesudah_login/shirt.jpg') }}';">
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
                                        <button class="btn btn-white text-danger fw-bold px-2 change-qty" 
                                                type="button" 
                                                data-action="decrease" 
                                                data-id="{{ $item->id }}">-</button>
                                        
                                        <input type="text" 
                                            class="form-control text-center border-0 bg-transparent p-1" 
                                            value="{{ $item->quantity }}" 
                                            id="qty-{{ $item->id }}" 
                                            readonly>
                                        
                                        <button class="btn btn-white text-danger fw-bold px-2 change-qty" 
                                                type="button" 
                                                data-action="increase" 
                                                data-id="{{ $item->id }}">+</button>
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
                        <a href="{{ route('checkout.index') }}" 
                            class="btn btn-danger fw-bold py-2 fs-5" 
                            style="border-radius: 8px;"
                            id="btn-checkout"
                            data-count="{{ $totalItems ?? 0 }}">
                                Buy Now
                        </a>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        
        $('.change-qty').click(function(e) {
            e.preventDefault();
            
            let btn = $(this);
            let id = btn.data('id');
            let action = btn.data('action');
            let input = $('#qty-' + id);
            let currentQty = parseInt(input.val());
            let newQty = currentQty;

            if (action === 'increase') {
                newQty = currentQty + 1;
            } else if (action === 'decrease') {
                if (currentQty > 1) {
                    newQty = currentQty - 1;
                } else {
                    Swal.fire({
                        title: 'Hapus produk?',
                        text: "Jumlah barang sudah 1. Apakah Anda ingin menghapusnya dari keranjang?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6', 
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Menghapus...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            $.ajax({
                                url: "/cart/remove/" + id,
                                type: "POST",
                                data: {
                                    _method: 'DELETE',
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    location.reload(); 
                                },
                                error: function(xhr) {
                                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus barang.', 'error');
                                }
                            });
                        }
                    });
                    
                    return;
                }
            }

            input.val(newQty);
            btn.prop('disabled', true);

            $.ajax({
                url: "/cart/update/" + id,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    quantity: newQty
                },
                success: function(response) {
                    location.reload(); 
                },
                error: function(xhr) {
                    input.val(currentQty); 
                    btn.prop('disabled', false);
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    Toast.fire({
                        icon: 'error',
                        title: 'Gagal mengupdate keranjang'
                    });
                }
            });
        });

        $('#btn-checkout').click(function(e) {
            let totalItems = $(this).data('count');
            if (!totalItems || totalItems <= 0) {
                e.preventDefault(); 
                Swal.fire({
                    title: 'Keranjang Kosong!',
                    text: "Anda belum memilih barang apapun. Yuk belanja dulu!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ke Halaman Belanja',
                    cancelButtonText: 'Nanti Saja'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('home') }}"; 
                    }
                });
            }
        });
    });
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endsection