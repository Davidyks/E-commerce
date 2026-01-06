<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\CartItem;
use App\Models\UserAddress;
use App\Models\Shipping;
use App\Models\Voucher;
use App\Models\FlashSale;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 
use stdClass; 

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $directBuy = session('direct_buy'); 
        $cartItems = collect([]);

        if ($directBuy) {
            $product = Product::with('seller')->find($directBuy['product_id']);
            if (!$product) return redirect()->back()->with('error', 'Product not found.');

            $tempItem = new stdClass();
            $tempItem->id = null; 
            $tempItem->quantity = $directBuy['quantity'];
            $tempItem->product = $product; 
            
            $tempItem->product_variant_id = $directBuy['variant_id'] ?? null;
            $tempItem->variant = $tempItem->product_variant_id ? ProductVariant::find($tempItem->product_variant_id) : null;
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
            }

            if($cartItems->isEmpty()) {
               return redirect()->route('cart.index')->with('error', 'No items selected.');
            }
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $product = $item->product;
            $flashsale = FlashSale::where(function ($q) use ($item){
                    if($item->product_variant_id){
                        $q->where('product_variant_id', $item->product_variant_id)
                            ->whereNull('product_id');
                    } else {
                        $q->where('product_id', $item->product->id)
                            ->whereNull('product_variant_id');
                    }
                })->where('start_time', '<=', now())->where('end_time', '>', now())->lockForUpdate()->first();

            $normalPrice = $item->variant ? $item->variant->price : $product->price;

            $item->display_price = $normalPrice;
            $item->flash_info = null;

            if($flashsale && $flashsale->flash_stock > 0){
                $flashPriceQuantity = min($item->quantity, $flashsale->flash_stock);
                $normalPriceQuantity = $item->quantity - $flashPriceQuantity;

                $item->display_price = $flashsale->flash_price;
                $item->flash_info = [
                    'flash_qty'   => $flashPriceQuantity,
                    'normal_qty'  => $normalPriceQuantity,
                    'flash_price' => $flashsale->flash_price,
                    'normal_price'=> $normalPrice,
                ];

                $subtotal += ($flashPriceQuantity * $flashsale->flash_price) + ($normalPriceQuantity * $normalPrice);
            } else {
                $subtotal += $item->quantity * $item->display_price;
            }
        }

        $groupedItems = $cartItems->groupBy(function ($item) {
            return $item->product->seller_id;
        });

        $appliedSession = session('applied_vouchers', []);
        $validVouchers = [];
        $discountAmount = 0;

        foreach ($appliedSession as $code) {
            $voucher = Voucher::where('code', $code)->first();
            if ($voucher && $voucher->isValid($user, $subtotal)) {
                $validVouchers[] = $code;
                $discountAmount += $voucher->calculateDiscount($subtotal);
            }
        }
        session()->put('applied_vouchers', $validVouchers);
        if ($discountAmount > $subtotal) $discountAmount = $subtotal;

        $mainAddress = UserAddress::where('user_id', $user->id)->where('is_default', true)->first() 
                       ?? UserAddress::where('user_id', $user->id)->first();

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
        
        $totalPay = ($subtotal + $insuranceFee + $applicationFee) - $discountAmount;
        if($totalPay < 0) $totalPay = 0;

        $paymentMethods = [
            ['code' => 'bca_va', 'name' => 'BCA Virtual Account', 'logo' => asset('asset/images/sebelum_login/bca.png'), 'fee' => 0.10],
            ['code' => 'mandiri_va', 'name' => 'Mandiri Virtual Account', 'logo' => asset('asset/images/sebelum_login/mandiri.png'), 'fee' => 0.10],
            ['code' => 'gosend_pay', 'name' => 'GoPay', 'logo' => asset('asset/images/sebelum_login/gopay.jpg'), 'fee' => 0],
            ['code' => 'bni_va', 'name' => 'BNI Virtual Account', 'logo' => asset('asset/images/sebelum_login/bni.png'), 'fee' => 0.10],
            ['code' => 'bri_va', 'name' => 'BRI Virtual Account', 'logo' => asset('asset/images/sebelum_login/bri.png'), 'fee' => 0.10],
        ];

        return view('user.checkout', compact(
            'groupedItems', 'cartItems', 'subtotal', 'discountAmount', 'shippings', 'mainAddress', 
            'insuranceFee', 'applicationFee', 'totalPay',
            'paymentMethods', 'availableVouchers'
        ));
    }

    public function applyVoucher(Request $request)
    {
        $request->validate(['code' => 'required']);
        $user = Auth::user();
        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) return redirect()->back()->with('error', 'Kode voucher tidak valid!');

        $directBuy = session('direct_buy');
        $subtotal = 0;
        if ($directBuy) {
            $product = Product::find($directBuy['product_id']);
            $variant = $directBuy['variant_id']
                ? ProductVariant::find($directBuy['variant_id'])
                : null;

            $qty = $directBuy['quantity'];
            $normalPrice = $variant ? $variant->price : $product->price;

            $flashsale = FlashSale::where(function ($q) use ($directBuy, $product) {
                if ($directBuy['variant_id']) {
                    $q->where('product_variant_id', $directBuy['variant_id']);
                } else {
                    $q->where('product_id', $product->id)
                    ->whereNull('product_variant_id');
                }
            })
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now())
            ->where('flash_stock', '>', 0)
            ->first();

            if ($flashsale) {
                $flashQty = min($qty, $flashsale->flash_stock);
                $normalQty = $qty - $flashQty;

                $subtotal =
                    ($flashQty * $flashsale->flash_price) +
                    ($normalQty * $normalPrice);
            } else {
                $subtotal = $qty * $normalPrice;
            }
        }

        if (!$voucher->isValid($user, $subtotal)) return redirect()->back()->with('error', 'Min. belanja tidak terpenuhi.');
        session()->put('applied_vouchers', [$voucher->code]);
        return redirect()->back()->with('success', 'Voucher berhasil!');
    }

    public function removeVoucher()
    {
        session()->forget('applied_vouchers');
        return redirect()->back()->with('success', 'Voucher dilepas.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required',
            'shipping_id' => 'required|array' 
        ]);
        
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $directBuy = session('direct_buy'); 
            $itemsToProcess = collect([]);

            if ($directBuy) {
                $product = Product::find($directBuy['product_id']);
                $itemObj = new stdClass();
                $itemObj->product = $product;
                $itemObj->product_id = $product->id;
                $itemObj->variant_id = $directBuy['variant_id'] ?? null;
                $itemObj->quantity = $directBuy['quantity'];
                
                $v = ProductVariant::find($itemObj->variant_id);
                $normalPrice = $itemObj->variant_id ? $v->price : $product->price;
                $itemObj->price = $normalPrice;
                $itemObj->seller_id = $product->seller_id;
                $itemsToProcess->push($itemObj);

            } else {
                if ($request->has('cart_item_ids')) {
                    $cartItems = CartItem::with('product')->whereIn('id', $request->cart_item_ids)->get();
                    foreach($cartItems as $cItem) {
                        $itemObj = new stdClass();
                        $itemObj->product = $cItem->product;
                        $itemObj->product_id = $cItem->product_id;
                        $itemObj->variant_id = $cItem->product_variant_id;
                        $itemObj->quantity = $cItem->quantity;
                        $itemObj->price = $cItem->price;
                        $itemObj->seller_id = $cItem->product->seller_id;
                        $itemsToProcess->push($itemObj);
                    }
                }
            }

            $groupedBySeller = $itemsToProcess->groupBy('seller_id');
            
            $transactionGroupId = 'TRX-' . time();

            foreach ($groupedBySeller as $sellerId => $items) {
                
                $shippingId = $request->shipping_id[$sellerId] ?? null;
                if(!$shippingId) throw new \Exception("Pengiriman untuk salah satu toko belum dipilih.");
                
                $shipping = Shipping::find($shippingId);
                $shippingCost = $shipping ? $shipping->base_cost : 0;

                $order = new Order();
                $order->user_id = $user->id;
                $order->seller_id = $sellerId; 
                $order->shipping_id = $shippingId;
                $order->order_code = 'ORD-' . strtoupper(Str::random(8)) . '-' . $sellerId; 
                $order->shipping_cost = $shippingCost;             
                $order->payment_method = $request->payment_method;
                $order->payment_status = 'pending'; 
                $order->notes = $transactionGroupId;
                $order->subtotal=0;
                $order->total_price=0;
                $order->save();

                $storeSubtotal = 0;

                foreach ($items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    $variant = null;

                    if($item->variant_id){
                        $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
                        $availableStock = $variant->stock;
                    } else {
                        $availableStock = $product->stock;
                    }

                    if($item->quantity > $availableStock){
                        throw new \Exception("Stok produk tidak mencukupi.");
                    }

                    $flashsale = FlashSale::where(function ($q) use ($item){
                        if($item->variant_id){
                            $q->where('product_variant_id', $item->variant_id)
                                ->whereNull('product_id');
                        } else {
                            $q->where('product_id', $item->product->id)
                                ->whereNull('product_variant_id');
                        }
                    })->where('start_time', '<=', now())->where('end_time', '>', now())->lockForUpdate()->first();

                    $flashPriceQuantity = 0;
                    $normalPriceQuantity = $item->quantity;

                    if($flashsale && $flashsale->flash_stock > 0){
                        $flashPriceQuantity = min($item->quantity, $flashsale->flash_stock);
                        $normalPriceQuantity = $item->quantity - $flashPriceQuantity;
                    }

                    if($flashPriceQuantity > 0){
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->variant_id,
                            'quantity' => $flashPriceQuantity,
                            'price' => $flashsale->flash_price,
                            'seller_id' => $sellerId,                            
                        ]);

                        $flashsale->decrement('flash_stock', $flashPriceQuantity);
                        $storeSubtotal += $flashPriceQuantity * $flashsale->flash_price;
                    }

                    if($normalPriceQuantity > 0){
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->variant_id,
                            'quantity' => $normalPriceQuantity,
                            'price' => $item->price,
                            'seller_id' => $sellerId,
                        ]);

                        $storeSubtotal += $normalPriceQuantity * $item->price;
                    }

                    $totalQuantity = $flashPriceQuantity + $normalPriceQuantity;

                    if($variant){
                        $variant->decrement('stock', $totalQuantity);
                    }

                    $product->decrement('stock', $totalQuantity);
                    $product->increment('sold_count', $totalQuantity);
                }

                $order->subtotal = $storeSubtotal;
                $order->total_price = $storeSubtotal + $shippingCost;
                $order->save();
            }

            if ($request->has('cart_item_ids')) {
                CartItem::whereIn('id', $request->cart_item_ids)
                    ->whereHas('cart', fn($q) => $q->where('user_id', $user->id))
                    ->delete();
            }

            session()->forget('direct_buy');
            session()->forget('applied_vouchers');

            DB::commit(); 
            return redirect()->route('home')->with('success', 'Transaksi berhasil! Pesanan telah dibuat per toko.');

        } catch (\Exception $e) {
            DB::rollBack(); 
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function addAddress(Request $request) { 
        $user = Auth::user();
        UserAddress::create(array_merge($request->all(), ['user_id' => $user->id, 'is_default' => false]));
        return redirect()->back()->with('success', 'Address saved.');
    }
}