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
            
            $tempItem->variant_id = $directBuy['variant_id'] ?? null;
            $tempItem->variant = $tempItem->variant_id ? ProductVariant::find($tempItem->variant_id) : null;
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

        foreach ($cartItems as $item) {
            $product = $item->product;
            $fs = FlashSale::where('product_id', $product->id)
                ->where('start_time', '<=', now())->where('end_time', '>', now())->first();

            $normalPrice = $item->variant ? $item->variant->price : $product->price;
            $currentStock = $item->variant ? $item->variant->stock : $product->stock;

            if ($fs && $fs->flash_stock > 0) {
                $item->price = $fs->flash_price;
                if ($item->quantity > $fs->flash_stock) $item->quantity = $fs->flash_stock;
            } else {
                $item->price = $normalPrice;
                if ($item->quantity > $currentStock) $item->quantity = $currentStock;
            }
            if ($item->id) $item->save();
        }

        $groupedItems = $cartItems->groupBy(function ($item) {
            return $item->product->seller_id;
        });

        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

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
            ['code' => 'shopeepay', 'name' => 'ShopeePay', 'logo' => asset('asset/images/sebelum_login/shopeepay.png'), 'fee' => 0.05],
            ['code' => 'cod', 'name' => 'Cash on Delivery', 'logo' => asset('asset/images/sebelum_login/cod.png'), 'fee' => 0],
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
            $price = $product->price;
            if(isset($directBuy['variant_id'])) {
                $v = ProductVariant::find($directBuy['variant_id']);
                if($v) $price = $v->price;
            }
            $subtotal = $price * $directBuy['quantity'];
        } else {
            $cart = $user->cart;
            if($cart) $subtotal = $cart->items->sum(fn($i) => $i->price * $i->quantity);
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
                
                if ($itemObj->variant_id) {
                     $v = ProductVariant::find($itemObj->variant_id);
                     $itemObj->price = $v->price;
                } else {
                     $itemObj->price = $product->price;
                }
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

                $storeSubtotal = $items->sum(fn($i) => $i->price * $i->quantity);
                
                $shipping = Shipping::find($shippingId);
                $shippingCost = $shipping ? $shipping->base_cost : 0;

                $storeTotal = $storeSubtotal + $shippingCost; 

                $order = new Order();
                $order->user_id = $user->id;
                $order->seller_id = $sellerId; 
                $order->shipping_id = $shippingId;
                $order->order_code = 'ORD-' . strtoupper(Str::random(8)) . '-' . $sellerId; 
                
                $order->subtotal = $storeSubtotal;
                $order->shipping_cost = $shippingCost;
                $order->total_price = $storeTotal;
                
                $order->payment_method = $request->payment_method;
                $order->payment_status = 'pending'; 
                $order->notes = $transactionGroupId; 
                $order->save();

                foreach ($items as $item) {
                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->product_id = $item->product_id;
                    $orderItem->quantity = $item->quantity;
                    $orderItem->price = $item->price;
                    $orderItem->product_variant_id = $item->variant_id ?? null;
                    $orderItem->seller_id = $sellerId; 
                    $orderItem->save();

                    $realProduct = Product::find($item->product_id); 
                    $realProduct->increment('sold_count', $item->quantity);

                    if ($item->variant_id) {
                        $realVariant = ProductVariant::find($item->variant_id);
                        if ($realVariant) {
                            if ($realVariant->stock >= $item->quantity) {
                                $realVariant->decrement('stock', $item->quantity);
                            }
                            if ($realProduct->stock >= $item->quantity) {
                                $realProduct->decrement('stock', $item->quantity);
                            }
                        }
                    } else {
                        if ($realProduct->stock >= $item->quantity) {
                            $realProduct->decrement('stock', $item->quantity);
                        }
                    }
                }
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