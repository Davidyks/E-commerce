<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Voucher;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        session()->forget('direct_buy');

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cartItems = $cart->items()->with(['product.seller', 'variant'])->get();

        foreach ($cartItems as $item) {
            $product = $item->product;
            
            $fs = FlashSale::where('product_id', $product->id)
                ->where('start_time', '<=', now())
                ->where('end_time', '>', now())
                ->first();

            $normalPrice = $item->variant ? $item->variant->price : $product->price;
            $currentPrice = ($fs && $fs->flash_stock > 0) ? $fs->flash_price : $normalPrice;

            if ($item->price != $currentPrice) {
                $item->price = $currentPrice;
                $item->save(); 
            }
        }

        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

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
            'appliedVoucherModels' 
        ));
    }

    public function UpdateQuantity(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $item = CartItem::find($id);

        if(!$item) return response()->json(['status' => 'error', 'message' => 'Item not found'], 404);

        $product = $item->product;
        $fs = FlashSale::where('product_id', $product->id)
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now())
            ->first();

        $limitStock = $item->variant ? $item->variant->stock : $product->stock;
        if ($fs && $fs->flash_stock > 0) $limitStock = $fs->flash_stock;

        if ($request->quantity > $limitStock) {
            return response()->json(['status' => 'error', 'message' => 'Stok max: ' . $limitStock], 400);
        }

        $item->quantity = $request->quantity;
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
        session()->put('direct_buy', [
            'product_id' => $id,
            'quantity' => $request->quantity ?? 1,
            'variant_id' => $request->variant_id ?? null,
        ]);

        return redirect()->route('checkout.index');
    }
}