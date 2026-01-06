<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function index()
    {
        session()->forget('direct_buy');

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cartItems = $cart->items()->with(['product.seller', 'variant'])->get();
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            
            $flashsale = Flashsale::where(function ($q) use ($item, $product){
                if($item->product_variant_id){
                    $q->where('product_variant_id', $item->product_variant_id);
                } else {
                    $q->where('product_id', $product->id)
                        ->whereNull('product_variant_id');
                }
            })
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now())
            ->where('flash_stock', '>', 0)
            ->first();

            $normalPrice = $item->variant ? $item->variant->price : $product->price;

            $item->display_price = $normalPrice;
            $item->line_total = 0;
            $item->flash_info = null;

            if($flashsale){
                $flashQty = min($item->quantity, $flashsale->flash_stock);
                $normalQty = $item->quantity - $flashQty;
                $item->display_price = $flashsale->flash_price;
                
                $item->flash_info = [
                    'flash_qty' => $flashQty,
                    'normal_qty' => $normalQty,
                    'flash_price' => $flashsale->flash_price,
                    'normal_price' => $normalPrice,
                ];

                $item->line_total = ($flashQty * $flashsale->flash_price) + ($normalQty * $normalPrice);
            } else {
                $item->line_total = $normalPrice * $item->quantity;
            }

            $subtotal += $item->line_total;
        }

        $appliedSession = session('applied_vouchers', []);
        $validVouchers = [];
        $appliedVoucherModels = collect([]);
        $discountAmount = 0;
        $voucherRemoved = false;

        foreach ($appliedSession as $type => $code) {
            $voucher = Voucher::where('code', $code)->first();

            if ($voucher && $subtotal >= $voucher->min_purchase && $voucher->isValid($user, $subtotal)) {
                $validVouchers[$type] = $code;
                $discountAmount += $voucher->calculateDiscount($subtotal);
                $appliedVoucherModels->push($voucher); 
            } else {
                $voucherRemoved = true;
            }
        }

        session()->put('applied_vouchers', $validVouchers);

        if ($voucherRemoved) {
            session()->flash('error', 'Voucher dilepas otomatis karena total belanja kurang dari batas minimal.');
        }

        if ($discountAmount > $subtotal) $discountAmount = $subtotal;
        $finalTotal = $subtotal - $discountAmount;
        $totalItems = $cartItems->sum('quantity');

        $groupedItems = $cartItems->groupBy(fn($item) => $item->product->seller_id);
        
        $usedVoucherIds = $user->vouchers->pluck('id')->toArray();
        $availableVouchers = Voucher::where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->whereNotIn('id', $usedVoucherIds)->get();

        $voucherCode = !empty($validVouchers) ? implode(', ', $validVouchers) : null;

        return view('user.cart', compact(
            'cartItems', 'groupedItems', 'subtotal', 'discountAmount', 
            'finalTotal', 'totalItems', 'availableVouchers', 'voucherCode',
            'appliedVoucherModels',
        ));
    }

    public function UpdateQuantity(Request $request, $id)
    {
        $item = CartItem::find($id);
        $product = $item->product;

        $request->validate(['quantity' => 'required|integer|min:'.$product->min_order_qty]);
        if(!$item) return response()->json(['status' => 'error', 'message' => 'Item not found'], 404);

        $newQty = $request->quantity;
        $limitStock = $item->variant ? $item->variant->stock : $product->stock;

        if ($request->quantity > $limitStock) {
            return response()->json(['status' => 'error', 'message' => 'Stok max: ' . $limitStock], 400);
        }

        $item->quantity = $newQty;
        $item->save();

        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();
        return redirect()->back()->with('success', 'Item removed from cart');
    }

    public function applyVoucher(Request $request) 
    {
        $request->validate(['code' => 'required']);
        $user = Auth::user();
        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) return redirect()->back()->with('error', 'Kode voucher tidak valid!');

        $cart = Cart::where('user_id', $user->id)->first();
        if(!$cart || $cart->items->isEmpty()) return redirect()->back()->with('error', 'Keranjang kosong.');
        
        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

        if (!$voucher->isValid($user, $subtotal)) {
            return redirect()->back()->with('error', 'Syarat voucher tidak terpenuhi (Min. Belanja kurang).');
        }

        $type = $voucher->seller_id ? 'store' : 'platform';
        $appliedSession = session('applied_vouchers', []);
        $appliedSession[$type] = $voucher->code;
        session()->put('applied_vouchers', $appliedSession);

        return redirect()->back()->with('success', 'Voucher berhasil digunakan!');
    }
    
    public function removeVoucher() 
    {
        session()->forget('applied_vouchers');
        return redirect()->back()->with('success', 'Voucher dilepas.');
    }
    
    public function buyNow(Request $request, $id)
    {
        $product = Product::findOrFail(id: $request->product_id);

        $request->validate([
            'quantity' => 'required|numeric|min:'.$product->min_order_qty,
            'product_id' => 'required|exists:products,id',
            'variant_id' => [
                'nullable',
                Rule::exists('product_variants','id')
                    ->where('product_id', $product->id)
            ],
        ], [
            'quantity.min' => 'Purchase at least :min item(s).',
        ]);

        if ($product->variants()->exists() && !$request->filled('variant_id')) {
            return back()
                ->withErrors(['variant_id' => 'Please select a variant first.'])
                ->withInput();
        }

        session()->put('direct_buy', [
            'product_id' => $id,
            'quantity' => $request->quantity ?? 1,
            'variant_id' => $request->variant_id ?? null,
        ]);

        return redirect()->route('checkout.index');
    }
}