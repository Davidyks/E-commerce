@extends('layout.sesudah_login.master')

@section('title', 'Cart')
@section('css', 'css/cart.css')
@section('content')
<div class="container py-4 px-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger fw-bold mb-0">@lang('messages.cart')</h3>
        <a href="{{ route('home') }}" class="fs-6 fw-bold text-decoration-none" style="color: #e63939;">
            @lang('messages.back')
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div class="form-check d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="selectAll" style="width: 1.2em; height: 1.2em;">
                        <label class="form-check-label fw-bold" for="selectAll">
                            @lang('messages.choose_all') <span class="text-muted fw-normal">({{ $totalItems ?? 0 }})</span>
                        </label>
                    </div>
                </div>
            </div>

            @forelse($groupedItems as $sellerId => $items)
                <div class="card border-0 shadow-sm mb-3 cart-card" style="border-radius: 8px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3 fw-bold store-name">
                            <i class="bi bi-shop me-2"></i>
                            <span>{{ $items->first()->seller->store_name ?? 'Store Name' }}</span>
                        </div>

                        @foreach($items as $item)
                        <div class="row border-bottom pb-3 mb-3">
                            <div class="col-1">
                                <input class="form-check-input item-checkbox" 
                                       type="checkbox" 
                                       style="width: 1.2em; height: 1.2em;"
                                       value="{{ $item->id }}"
                                       data-line-total="{{ $item->line_total }}">
                            </div>
                            <div class="col-3 col-md-2">
                                @php
                                    $imgUrl = $item->product->product_image;
                                    if (!Illuminate\Support\Str::startsWith($imgUrl, 'http')) {
                                        $imgUrl = asset('storage/' . $imgUrl);
                                    }
                                @endphp
                                <img src="{{ $imgUrl }}" class="img-fluid rounded" 
                                     style="width: 100%; aspect-ratio: 1/1; object-fit: contain; border: 1px solid #eee;"
                                     onerror="this.onerror=null;this.src='{{ asset('asset/images/sesudah_login/shirt.jpg') }}';">
                            </div>
                            <div class="col-5">
                                <h6 class="mb-1 fw-normal">{{ $item->product->name }}</h6>
                                @if($item->variant)
                                    <small class="text-muted d-block">Variant: {{ $item->variant->variant_name }}</small>
                                @endif

                                <form id="delete-form-{{ $item->id }}" action="{{ route('cart.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-link text-muted p-0 text-decoration-none mt-1" 
                                            style="font-size: 0.8rem;" 
                                            onclick="confirmRemoveItem({{ $item->id }})">
                                        <i class="bi bi-trash"></i> @lang('messages.remove')
                                    </button>
                                </form>
                            </div>
                            <div class="col-3 col-md-4 text-end">
                                <div class="text-danger fw-bold price">
                                    ${{ number_format($item->display_price, 2) }}
                                </div>
                                @if($item->flash_info)
                                    <small class="text-muted d-block qtyPrice">
                                        ${{  number_format($item->flash_info['flash_price'], 2) }} x{{ $item->flash_info['flash_qty'] }}
                                    </small>

                                    @if($item->flash_info['normal_qty'] > 0)
                                        <small class="text-muted d-block qtyPrice">
                                            ${{  number_format($item->flash_info['normal_price'], 2) }} x{{ $item->flash_info['normal_qty'] }}
                                        </small>
                                    @endif
                                @else
                                    <small class="text-muted d-block qtyPrice">
                                        ${{ number_format($item->display_price, 2) }} x{{ $item->quantity }}
                                    </small>
                                @endif

                                <div class="d-flex justify-content-end align-items-center gap-3 mt-2">
                                    <div class="input-group input-group-sm qtyInput">
                                        <button class="btn btn-white text-danger fw-bold px-md-2 px-1 change-qty" type="button" data-action="decrease" data-id="{{ $item->id }}">-</button>
                                        <input type="text" class="form-control text-center border-0 bg-transparent p-1" value="{{ $item->quantity }}" id="qty-{{ $item->id }}" readonly>
                                        <button class="btn btn-white text-danger fw-bold px-md-2 px-1 change-qty" type="button" data-action="increase" data-id="{{ $item->id }}">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <h4 class="fw-bold mt-3">Cart is Empty</h4>
                        <a href="{{ route('home') }}" class="btn btn-danger px-5 py-2 fw-bold mt-3">@lang('messages.start_shopping')</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-danger mb-4">@lang('messages.shopping_summary')</h5>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">@lang('messages.voucher_code')</label>
                        <div id="applied-voucher-view" class="{{ (isset($voucherCode) && $voucherCode) ? '' : 'd-none' }}">
                            <div class="d-flex justify-content-between align-items-center p-2 border border-success rounded bg-light">
                                
                                <div class="d-flex align-items-center text-success" style="overflow: hidden;">
                                    <i class="bi bi-ticket-fill me-2 flex-shrink-0"></i> 
                                    <span class="fw-bold text-truncate" title="{{ $voucherCode ?? '' }}" style="max-width: 140px;">
                                        {{ $voucherCode ?? '' }}
                                    </span>
                                </div>
                                
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <button type="button" class="btn btn-sm btn-outline-success fw-bold py-0 px-2" 
                                            style="height: 24px; line-height: 1;"
                                            data-bs-toggle="modal" data-bs-target="#voucherModal"
                                            title="Add another voucher">
                                        +
                                    </button>

                                    <button type="button" class="btn btn-sm text-danger fw-bold p-0" onclick="confirmRemoveVoucher()" title="Remove All">
                                        <i class="bi bi-x-circle-fill fs-5"></i>
                                    </button>
                                </div>
                                
                                <form id="remove-voucher-form" action="{{ route('cart.voucher.remove') }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </div>

                        <div id="no-voucher-view" class="{{ (isset($voucherCode) && $voucherCode) ? 'd-none' : '' }}">
                            <button type="button" class="btn d-flex justify-content-between align-items-center px-3 py-2 w-100" 
                                    style="background-color: #fcebeb; color: #333; border: 1px solid #f5c6cb; border-radius: 8px;"
                                    data-bs-toggle="modal" data-bs-target="#voucherModal">
                                <span class="d-flex align-items-center"><i class="bi bi-ticket-perforated-fill text-danger me-2"></i> @lang('messages.use_coupon')</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </button>
                        </div>
                    </div>

                    <hr class="text-muted">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold" id="summary-subtotal">$0.00</span>
                    </div>

                    <div id="summary-discount-row" class="d-flex justify-content-between align-items-center mb-2 text-success {{ (isset($discountAmount) && $discountAmount > 0) ? '' : 'd-none' }}">
                        <span>Discount</span>
                        <span class="fw-bold" id="summary-discount" data-val="{{ $discountAmount ?? 0 }}">
                            - ${{ number_format($discountAmount ?? 0, 2) }}
                        </span>
                    </div>

                    <hr class="text-muted my-3">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold text-danger fs-4" id="summary-total">$0.00</span>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-danger fw-bold py-2 fs-5" style="border-radius: 8px;" id="btn-checkout">@lang('messages.buy_now')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="voucherModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="voucherModalLabel">@lang('messages.avail_coupon')</h5>
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
                            <small class="text-muted">Min. Purchase: ${{ number_format($voucher->min_purchase, 2) }}</small>
                        </div>
                        <form action="{{ route('cart.voucher.apply') }}" method="POST">
                            @csrf
                            <input type="hidden" name="code" value="{{ $voucher->code }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">@lang('messages.apply')</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-5"><p class="text-muted">@lang('messages.no_voucher').</p></div>
        @endif
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const appliedVouchers = @json($appliedVoucherModels ?? []);

        function saveCheckedState() {
            let checkedIds = [];
            $('.item-checkbox:checked').each(function() { checkedIds.push($(this).val()); });
            localStorage.setItem('cart_checked_items', JSON.stringify(checkedIds));
        }

        function restoreCheckedState() {
            let saved = localStorage.getItem('cart_checked_items');
            if (saved) {
                let ids = JSON.parse(saved);
                $('.item-checkbox').each(function() {
                    if (ids.includes($(this).val())) $(this).prop('checked', true);
                    else $(this).prop('checked', false);
                });
                
                if ($('.item-checkbox:not(:checked)').length > 0) $('#selectAll').prop('checked', false);
                else if ($('.item-checkbox').length > 0) $('#selectAll').prop('checked', true);
            } else {
                $('.item-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
            }
            recalculateTotal();
        }

        function recalculateTotal() {
            let subtotal = 0;
            let totalItems = 0;

            $('.item-checkbox:checked').each(function() {
                let lineTotal = parseFloat($(this).data('line-total'));
                subtotal += lineTotal;
            });

            let isVoucherValid = true;
            if (appliedVouchers.length > 0) {
                appliedVouchers.forEach(v => {
                    let minP = parseFloat(v.min_purchase);
                    if (subtotal < minP) isVoucherValid = false;
                });
            } else {
                isVoucherValid = false;
            }

            if (isVoucherValid && appliedVouchers.length > 0) {
                $('#applied-voucher-view').removeClass('d-none');
                $('#no-voucher-view').addClass('d-none');
                $('#summary-discount-row').removeClass('d-none');
            } else {
                $('#applied-voucher-view').addClass('d-none');
                $('#no-voucher-view').removeClass('d-none');
                $('#summary-discount-row').addClass('d-none');
            }

            let discountElem = $('#summary-discount');
            let discount = 0;
            if (isVoucherValid && discountElem.length > 0) {
                discount = parseFloat(discountElem.data('val'));
            } else {
                discount = 0;
            }

            if(subtotal === 0) discount = 0;
            else if(discount > subtotal) discount = subtotal;
            
            let finalTotal = subtotal - discount;

            let formatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
            $('#summary-subtotal').text(formatter.format(subtotal));
            $('#summary-total').text(formatter.format(finalTotal));
            $('#btn-checkout').data('count', totalItems);
        }

        $('#selectAll').change(function() {
            $('.item-checkbox').prop('checked', $(this).is(':checked'));
            recalculateTotal();
            saveCheckedState();
        });

        $('.item-checkbox').change(function() {
            if (!$(this).is(':checked')) $('#selectAll').prop('checked', false);
            if ($('.item-checkbox:checked').length === $('.item-checkbox').length) $('#selectAll').prop('checked', true);
            recalculateTotal();
            saveCheckedState();
        });

        restoreCheckedState();

        $('.change-qty').click(function(e) {
            e.preventDefault();
            let btn = $(this);
            let id = btn.data('id');
            let action = btn.data('action');
            let input = $('#qty-' + id);
            let currentQty = parseInt(input.val());
            let newQty = currentQty;

            if (action === 'increase') newQty = currentQty + 1;
            else if (action === 'decrease') {
                if (currentQty > 1) newQty = currentQty - 1;
                else {
                    confirmRemoveItem(id);
                    return;
                }
            }
            
            input.val(newQty);
            btn.prop('disabled', true);
            saveCheckedState(); 

            $.ajax({
                url: "/cart/update/" + id, type: "POST",
                data: { _token: "{{ csrf_token() }}", quantity: newQty },
                success: function() { location.reload(); },
                error: function() { input.val(currentQty); btn.prop('disabled', false); }
            });
        });

        $('#btn-checkout').click(function(e) {
            e.preventDefault();
            let selectedIds = [];
            $('.item-checkbox:checked').each(function() { selectedIds.push($(this).val()); });

            if (selectedIds.length === 0) {
                Swal.fire({ title: 'No Items Selected!', text: "Please select an item.", icon: 'warning' });
                return;
            }
            localStorage.removeItem('cart_checked_items'); 
            window.location.href = "{{ route('checkout.index') }}?items=" + selectedIds.join(',');
        });
    });

    function confirmRemoveVoucher() {
        Swal.fire({
            title: '@lang('messages.remove') Voucher?',
            text: '@lang('messages.remove_voucher')?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '@lang('messages.yes_remove')!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('remove-voucher-form').submit();
            }
        });
    }

    function confirmRemoveItem(itemId) {
        Swal.fire({
            title: '@lang('messages.remove') @lang('messages.item')?',
            text: '@lang('messages.remove_item')?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '@lang('messages.yes_remove')!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Removing...', didOpen: () => Swal.showLoading() });
                document.getElementById('delete-form-' + itemId).submit();
            }
        });
    }
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endsection