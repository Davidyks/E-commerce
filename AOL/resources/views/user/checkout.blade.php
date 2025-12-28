@extends('layout.sesudah_login.master')

@section('title', 'Checkout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container py-4">
    <h3 class="fw-bold text-danger mb-4">Checkout</h3>
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold text-danger mb-3">Address</h5>
                            @if($mainAddress)
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-geo-alt-fill text-danger me-2 fs-5"></i>
                                    <span class="fw-bold">{{ $mainAddress->label }} - {{ $mainAddress->recipient_name }}</span>
                                </div>
                                <p class="text-muted small ms-4 mb-0">
                                    {{ $mainAddress->address }}<br>
                                    {{ $mainAddress->city }}, {{ $mainAddress->province }} {{ $mainAddress->postal_code }}<br>
                                    {{ $mainAddress->phone }}
                                </p>
                            @else
                                <div class="alert alert-warning mb-0">No default address found.</div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            Change
                        </button>
                    </div>
                </div>
            </div>

            @foreach($cartItems as $item)
                <div class="card shadow-sm mb-4" style="border: 2px solid #0d6efd;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-patch-check-fill text-danger me-2"></i>
                            <span class="fw-bold">{{ $item->product->seller->store_name ?? 'Seller Store' }}</span>
                        </div>

                        <div class="d-flex gap-3 mb-4">
                            @php
                                $imgUrl = $item->product->product_image;
                                if (!Illuminate\Support\Str::startsWith($imgUrl, 'http')) {
                                    $imgUrl = asset('storage/' . $imgUrl);
                                }
                            @endphp
                            <img src="{{ $imgUrl }}" 
                                class="rounded" 
                                style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #eee;"
                                onerror="this.onerror=null;this.src='{{ asset('asset/images/sesudah_login/shirt.jpg') }}';">
                            
                            <div class="w-100 d-flex justify-content-between">
                                <div>
                                    <h6 class="fw-semibold mb-1">{{ $item->product->name }}</h6>
                                    @if($item->variant)
                                        <small class="text-muted d-block">Variant: {{ $item->variant->variant_name }}</small>
                                    @endif
                                    <small class="text-muted">Quantity: {{ $item->quantity }}</small>
                                </div>
                                <div class="fw-bold text-danger">${{ number_format($item->price, 2) }}</div>
                            </div>
                        </div>
                        
                        <div class="bg-light p-3 rounded mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small"><i class="bi bi-truck me-1"></i> Delivery Option</span>
                            </div>
                            
                            <select class="form-select form-select-sm border-0 bg-white mb-2 fw-bold shipping-selector" 
                                    name="shipping_id[{{ $item->id ?? 'buynow_'.$loop->index }}]" 
                                    id="shipping_{{ $loop->index }}" 
                                    data-index="{{ $loop->index }}"
                                    onchange="calculateTotal()">
                                
                                @foreach($shippings as $ship)
                                    <option value="{{ $ship->id }}" 
                                            data-cost="{{ $ship->base_cost }}" 
                                            data-days="{{ $ship->estimated_days }}">
                                        {{ $ship->courier }} ({{ $ship->service }}) - ${{ number_format($ship->base_cost, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <small class="text-muted d-block ms-1" id="estimate_{{ $loop->index }}">
                                Estimated Arrival: {{ now()->addDays($shippings->first()->estimated_days ?? 3)->format('d F') }}
                            </small>
                        </div>

                        <div class="form-check mb-3 ms-1">
                            <input class="form-check-input bg-danger border-danger" type="checkbox" checked id="insurance_{{ $loop->index }}" disabled>
                            <label class="form-check-label small" for="insurance_{{ $loop->index }}">
                                Shipping Insurance (${{ number_format($insuranceFee ?? 0.50, 2) }})
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-md-4">
            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold text-danger mb-0">Payment Method</h5>
                        <button type="button" class="btn btn-link text-danger fw-bold small text-decoration-none p-0" 
                                data-bs-toggle="modal" data-bs-target="#paymentModal">
                            View All
                        </button>
                    </div>

                    <div id="payment-preview-list">
                        @foreach(array_slice($paymentMethods, 0, 3) as $index => $payment)
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom" 
                                 style="cursor: pointer;"
                                 onclick="selectPayment('{{ $payment['code'] }}')">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $payment['logo'] }}" style="width: 40px; height: 25px; object-fit: contain;">
                                    <label class="form-check-label fw-semibold small" style="cursor: pointer;">{{ $payment['name'] }}</label>
                                </div>
                                <input class="form-check-input border-secondary" type="radio" 
                                       name="payment_visual" 
                                       id="visual_{{ $payment['code'] }}"
                                       {{ $index === 0 ? 'checked' : '' }}>
                            </div>
                        @endforeach
                    </div>

                    <div id="selected-payment-display" class="alert alert-light border d-none text-center">
                        <small class="text-muted">Selected:</small><br>
                        <strong id="selected-payment-name" class="text-danger"></strong>
                    </div>

                    <div class="d-grid mt-2">
                        @if(session('applied_vouchers') && count(session('applied_vouchers')) > 0)
                            <div class="p-2 border border-danger rounded d-flex justify-content-between align-items-center" style="background-color: #ffeaea;">
                                
                                <div class="d-flex align-items-center text-danger">
                                    <i class="bi bi-ticket-perforated-fill fs-4 me-2"></i>
                                    <div style="line-height: 1.2;">
                                        <span class="d-block small text-muted" style="font-size: 0.7rem;">Voucher Applied:</span>
                                        <span class="fw-bold">{{ implode(', ', session('applied_vouchers')) }}</span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold py-0 px-2" style="height: 28px;" 
                                            data-bs-toggle="modal" data-bs-target="#voucherModal">
                                        +
                                    </button>

                                    <form action="{{ route('checkout.remove.voucher') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm text-secondary p-0 ms-1" title="Remove Voucher">
                                            <i class="bi bi-x-circle-fill fs-6"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <button type="button" class="btn btn-danger bg-opacity-10 text-danger border-0 d-flex justify-content-between align-items-center py-2 px-3" 
                                    style="background-color: #ffeaea;"
                                    data-bs-toggle="modal" data-bs-target="#voucherModal">
                                <span class="fw-bold small"><i class="bi bi-ticket-perforated-fill me-2"></i> Use coupons</span>
                                <i class="bi bi-chevron-right small"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Shopping summary</h5>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Price</span>
                        <span class="fw-bold">${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Shipping</span>
                        <span class="fw-bold" id="display_shipping">${{ number_format($defaultShippingCost * $cartItems->count(), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Application Fees</span>
                        <span class="fw-bold">${{ number_format($applicationFee, 2) }}</span>
                    </div>
                    
                    @if($discountAmount > 0)
                    <div class="d-flex justify-content-between mb-2 small text-success">
                        <span>Discount</span>
                        <span class="fw-bold">- ${{ number_format($discountAmount, 2) }}</span>
                    </div>
                    @endif
                    
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold">Shopping Total</span>
                        <span class="fw-bold fs-5" id="display_total">${{ number_format($totalPay, 2) }}</span>
                    </div>

                    <div class="d-grid">
                        <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
                            @csrf
                            <input type="hidden" name="total_price" id="input_total" value="{{ $totalPay }}">
                            <input type="hidden" name="payment_method" id="real_payment_method" value="{{ $paymentMethods[0]['code'] ?? '' }}">
                            
                            @foreach($cartItems as $item)
                                @if($item->id)
                                    <input type="hidden" name="cart_item_ids[]" value="{{ $item->id }}">
                                @endif
                            @endforeach
                            
                            <button type="button" onclick="confirmPayment()" class="btn btn-danger fw-bold py-2 w-100 fs-5">
                                Pay Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Select Payment Method</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            @foreach($paymentMethods as $index => $payment)
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom" 
                     style="cursor: pointer;"
                     onclick="selectPaymentFromModal('{{ $payment['code'] }}', '{{ $payment['name'] }}')">
                    
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $payment['logo'] }}" style="width: 50px; height: 30px; object-fit: contain;">
                        <div>
                            <label class="form-check-label fw-bold d-block" style="cursor: pointer;">{{ $payment['name'] }}</label>
                            @if($payment['fee'] > 0)
                                <small class="text-muted">Fee: ${{ number_format($payment['fee'], 2) }}</small>
                            @endif
                        </div>
                    </div>
                    <input class="form-check-input border-secondary" type="radio" 
                           name="payment_modal_radio" 
                           id="modal_radio_{{ $payment['code'] }}" 
                           {{ $index === 0 ? 'checked' : '' }}>
                </div>
            @endforeach
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">Confirm Selection</button>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Available Coupons</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body bg-light">
          @if(isset($availableVouchers) && count($availableVouchers) > 0)
              @foreach($availableVouchers as $voucher)
                  @php
                      $applied = session('applied_vouchers', []);
                      $isUsed = in_array($voucher->code, $applied);
                  @endphp

                  <div class="card mb-2 border-0 shadow-sm {{ $isUsed ? 'border border-danger' : '' }}">
                      <div class="card-body d-flex justify-content-between align-items-center">
                          <div>
                              <h6 class="fw-bold {{ $isUsed ? 'text-danger' : 'text-dark' }} mb-1">
                                  {{ $voucher->code }} 
                                  @if($isUsed) <i class="bi bi-check-circle-fill text-danger small"></i> @endif
                              </h6>
                              <small class="d-block fw-bold text-muted">{{ $voucher->title }}</small>
                              <small class="text-muted" style="font-size: 0.75rem;">
                                  Min. Spend: ${{ number_format($voucher->min_purchase, 2) }}
                              </small>
                          </div>
                          
                          @if($isUsed)
                              <button class="btn btn-sm btn-secondary fw-bold" disabled>Applied</button>
                          @else
                              <form action="{{ route('checkout.apply.voucher') }}" method="POST">
                                  @csrf
                                  <input type="hidden" name="code" value="{{ $voucher->code }}">
                                  <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">Apply</button>
                              </form>
                          @endif
                      </div>
                  </div>
              @endforeach
          @else
              <div class="text-center py-4 text-muted">No coupons available.</div>
          @endif
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('address.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                         <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Label Address</label>
                            <input type="text" name="label" class="form-control" placeholder="e.g. Home, Office" required>
                         </div>
                         <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Recipient Name</label>
                            <input type="text" name="recipient_name" class="form-control" value="{{ Auth::user()->name }}" required>
                         </div>
                         <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                         </div>
                         <div class="col-6">
                            <label class="form-label small fw-bold text-muted">City</label>
                            <input type="text" name="city" class="form-control" required>
                         </div>
                         <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Province</label>
                            <input type="text" name="province" class="form-control" required>
                         </div>
                         <div class="col-4">
                            <label class="form-label small fw-bold text-muted">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" required>
                         </div>
                         <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Full Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Street Name, House No..." required></textarea>
                         </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger fw-bold">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const subtotal = {{ $subtotal }};
    const insurance = {{ $insuranceFee }};
    const appFee = {{ $applicationFee }};
    const discount = {{ isset($discountAmount) ? $discountAmount : 0 }};

    function calculateTotal() {
        let totalShipping = 0;
        let cartItemCount = 0; 
        const selectors = document.querySelectorAll('.shipping-selector');
        
        selectors.forEach(select => {
            const selectedOption = select.options[select.selectedIndex];
            
            const cost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
            totalShipping += cost;

            const days = parseInt(selectedOption.getAttribute('data-days')) || 3;
            const index = select.getAttribute('data-index'); 
            
            updateEstimationDate(index, days);

            cartItemCount++; 
        });

        const totalInsurance = insurance * cartItemCount;

        let grandTotal = subtotal + totalShipping + totalInsurance + appFee - discount;
        
        if(grandTotal < 0) grandTotal = 0;

        document.getElementById('display_shipping').innerText = formatCurrency(totalShipping);
        document.getElementById('display_total').innerText = formatCurrency(grandTotal);
        
        document.getElementById('input_total').value = grandTotal;
    }

    function updateEstimationDate(index, days) {
        const estimateElement = document.getElementById('estimate_' + index);
        if (estimateElement) {
            const date = new Date();
            date.setDate(date.getDate() + days);
            
            const options = { day: 'numeric', month: 'long' };
            const formattedDate = date.toLocaleDateString('en-US', options);
            
            if(days === 0) {
                estimateElement.innerText = "Estimated Arrival: TODAY (Instant)";
            } else {
                estimateElement.innerText = "Estimated Arrival: " + formattedDate;
            }
        }
    }

    function formatCurrency(number) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(number);
    }

    document.addEventListener("DOMContentLoaded", function() {
        calculateTotal();
    });

    function selectPayment(code) {
        document.getElementById('real_payment_method').value = code;
        if(document.getElementById('visual_' + code)) document.getElementById('visual_' + code).checked = true;
        if(document.getElementById('modal_radio_' + code)) document.getElementById('modal_radio_' + code).checked = true;
    }

    function selectPaymentFromModal(code, name) {
        document.getElementById('real_payment_method').value = code;
        document.getElementById('modal_radio_' + code).checked = true;
        
        if(document.getElementById('visual_' + code)) {
            document.getElementById('visual_' + code).checked = true;
            document.getElementById('selected-payment-display').classList.add('d-none');
        } else {
            let radios = document.getElementsByName('payment_visual');
            radios.forEach(r => r.checked = false);
            document.getElementById('selected-payment-display').classList.remove('d-none');
            document.getElementById('selected-payment-name').innerText = name;
        }
    }

    function confirmPayment() {
        Swal.fire({
            title: 'Processing Order',
            text: 'Please wait while we process your payment...',
            icon: 'info',
            timer: 2000,
            showConfirmButton: false,
            willClose: () => {
                Swal.fire({
                    title: 'Payment Successful!',
                    text: 'Your order has been placed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('checkoutForm').submit();
                    }
                });
            }
        });
    }
</script>

@endsection