<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem;
use App\Models\UserAddress;
use App\Models\Shipping;
use App\Models\Voucher;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass; 

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $directBuy = session('direct_buy'); 

        if ($directBuy) {
            $product = \App\Models\Product::with('seller')->find($directBuy['product_id']);

            if (!$product) {
                return redirect()->back()->with('error', 'Product not found.');
            }

            $tempItem = new stdClass();
            $tempItem->id = null; 
            $tempItem->quantity = $directBuy['quantity'];
            $tempItem->product = $product; 
            
            $tempItem->variant_id = $directBuy['variant_id'] ?? null;
            $tempItem->variant = $tempItem->variant_id ? \App\Models\ProductVariant::find($tempItem->variant_id) : null;
            
            $tempItem->price = $tempItem->variant ? $tempItem->variant->price : $product->price;

            $cartItems = collect([$tempItem]);

        } else {
            $cart = $user->cart;
            
            if ($cart) {
                $query = $cart->items()->with(['product.seller', 'variant']);

                if ($request->has('items')) {
                    $selectedIds = explode(',', $request->items);
                    $query->whereIn('id', $selectedIds);
                }

                $cartItems = $query->get();
            } else {
                $cartItems = collect([]);
            }

            if($cartItems->isEmpty()) {
               return redirect()->route('cart.index')->with('error', 'No items selected for checkout.');
            }
        }

        foreach ($cartItems as $item) {
            $product = $item->product;
            
            $fs = FlashSale::where('product_id', $product->id)
                ->where('start_time', '<=', now())
                ->where('end_time', '>', now())
                ->first();

            $normalPrice = $item->variant ? $item->variant->price : $product->price;
            $currentStock = $item->variant ? $item->variant->stock : $product->stock;

            if ($fs && $fs->flash_stock > 0) {
                $item->price = $fs->flash_price;
                
                if ($item->quantity > $fs->flash_stock) {
                    session()->flash('error', "Flash Sale stock for {$product->name} limited. Qty adjusted.");
                    $item->quantity = $fs->flash_stock;
                }
            } else {
                $item->price = $normalPrice;
                
                if ($item->quantity > $currentStock) {
                    session()->flash('error', "Stock for {$product->name} limited. Qty adjusted.");
                    $item->quantity = $currentStock;
                }
            }

            if ($item->id) {
                $item->save();
            }
        }

        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

        $appliedSession = session('applied_vouchers', []);
        $validVouchers = [];
        $discountAmount = 0;
        $voucherRemoved = false;

        foreach ($appliedSession as $type => $code) {
            $voucher = Voucher::where('code', $code)->first();
            
            if ($voucher && $voucher->isValid($user, $subtotal)) {
                $validVouchers[$type] = $code;
                $discountAmount += $voucher->calculateDiscount($subtotal);
            } else {
                $voucherRemoved = true;
            }
        }

        session()->put('applied_vouchers', $validVouchers);

        if ($voucherRemoved) {
            session()->flash('error', 'Some vouchers removed because minimum spend requirement not met.');
        }

        if ($discountAmount > $subtotal) $discountAmount = $subtotal;

        $mainAddress = UserAddress::where('user_id', $user->id)->where('is_default', true)->first();
        if (!$mainAddress) {
            $mainAddress = UserAddress::where('user_id', $user->id)->first();
        }

        $shippings = Shipping::all();
        foreach($shippings as $ship) {
            $courierName = strtolower($ship->courier);
             if(str_contains($courierName, 'jne')) $ship->logo = asset('asset/images/sebelum_login/jne.png');
            elseif(str_contains($courierName, 'sicepat')) $ship->logo = asset('asset/images/sebelum_login/sicepat.png');
            elseif(str_contains($courierName, 'ninja')) $ship->logo = asset('asset/images/sebelum_login/ninja express.png');
            elseif(str_contains($courierName, 'gosend')) $ship->logo = asset('asset/images/sebelum_login/gosend.png');
            else $ship->logo = asset('asset/images/sebelum_login/delivery.png');
        }

        $usedVoucherIds = $user->vouchers->pluck('id')->toArray();
        $availableVouchers = Voucher::where('start_at', '<=', now())
                            ->where('end_at', '>=', now())
                            ->whereNotIn('id', $usedVoucherIds)
                            ->get();

        $insuranceFee = 0.50;   
        $applicationFee = 0.20; 
        $defaultShippingCost = $shippings->first()->base_cost ?? 0;

        $totalPay = ($subtotal + $defaultShippingCost + $insuranceFee + $applicationFee) - $discountAmount;
        if($totalPay < 0) $totalPay = 0;

        $paymentMethods = [
            ['code' => 'bca_va', 'name' => 'BCA Virtual Account', 'logo' => asset('asset/images/sebelum_login/bca.png'), 'fee' => 0.10],
            ['code' => 'mandiri_va', 'name' => 'Mandiri Virtual Account', 'logo' => asset('asset/images/sebelum_login/mandiri.png'), 'fee' => 0.10],
            ['code' => 'gosend_pay', 'name' => 'GoPay', 'logo' => asset('asset/images/sebelum_login/gopay.jpg'), 'fee' => 0],
            ['code' => 'bni_va', 'name' => 'BNI Virtual Account', 'logo' => asset('asset/images/sebelum_login/bni.png'), 'fee' => 0.10],
            ['code' => 'bri_va', 'name' => 'BRI Virtual Account', 'logo' => asset('asset/images/sebelum_login/bri.png'), 'fee' => 0.10],
            ['code' => 'Indomaret', 'name' => 'Indomaret', 'logo' => asset('asset/images/sebelum_login/indomare.png'), 'fee' => 0.05],
            ['code' => 'Alfamart', 'name' => 'Cash on Delivery', 'logo' => asset('asset/images/sebelum_login/alfamart.png'), 'fee' => 0.05],
        ];

        return view('user.checkout', compact(
            'cartItems', 'subtotal', 'discountAmount', 'shippings', 'mainAddress', 
            'insuranceFee', 'applicationFee', 'totalPay', 'defaultShippingCost',
            'paymentMethods', 'availableVouchers'
        ));
    }

    public function applyVoucher(Request $request)
    {
        $request->validate(['code' => 'required']);
        $user = Auth::user();
        
        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) {
            return redirect()->back()->with('error', 'Kode voucher tidak valid!');
        }

        $directBuy = session('direct_buy');
        $subtotal = 0;

        if ($directBuy) {
            $product = Product::find($directBuy['product_id']);
            if ($product) {
                $fs = FlashSale::where('product_id', $product->id)->where('start_time', '<=', now())->where('end_time', '>', now())->first();
                $price = ($fs && $fs->flash_stock > 0) ? $fs->flash_price : $product->price;

                if(isset($directBuy['variant_id'])) {
                    $variant = \App\Models\ProductVariant::find($directBuy['variant_id']);
                    if($variant) $price = ($fs && $fs->flash_stock > 0) ? $fs->flash_price : $variant->price;
                }
                $subtotal = $price * $directBuy['quantity'];
            }
        } else {
            $cart = $user->cart;
            if (!$cart || $cart->items->isEmpty()) return redirect()->back()->with('error', 'Keranjang kosong.');
            
            $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
        }

        if (!$voucher->isValid($user, $subtotal)) {
            return redirect()->back()->with('error', 'Voucher tidak valid (Minimal belanja kurang).');
        }
        
        $type = $voucher->seller_id ? 'store' : 'platform';
        $appliedSession = session('applied_vouchers', []);
        $appliedSession[$type] = $voucher->code;
        
        session()->put('applied_vouchers', $appliedSession);

        return redirect()->back()->with('success', 'Voucher berhasil dipasang!');
    }

    public function removeVoucher()
    {
        session()->forget('applied_vouchers');
        return redirect()->back()->with('success', 'Semua voucher dilepas.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'total_price' => 'required',
            'payment_method' => 'required',
        ]);

        $user = Auth::user();

        if ($request->has('cart_item_ids')) {
            CartItem::whereIn('id', $request->cart_item_ids)
                ->whereHas('cart', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->delete();
        }

        session()->forget('direct_buy');
        session()->forget('applied_vouchers');

        return redirect()->route('home')->with('success', 'Pesanan berhasil dibuat! Barang telah dihapus dari keranjang.');
    }

    public function addAddress(Request $request) { 
        $request->validate([
            'label' => 'required', 'recipient_name' => 'required', 'phone' => 'required',
            'address' => 'required', 'city' => 'required', 'province' => 'required', 'postal_code' => 'required',
        ]);
        
        $user = Auth::user();
        $isFirst = UserAddress::where('user_id', $user->id)->doesntExist();

        UserAddress::create([
            'user_id' => $user->id,
            'label' => $request->label,
            'recipient_name' => $request->recipient_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'is_default' => $isFirst,
        ]);

        return redirect()->back()->with('success', 'Alamat berhasil disimpan!');
    }
}