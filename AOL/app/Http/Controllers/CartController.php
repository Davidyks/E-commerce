<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use App\Services\VoucherService;
use App\Models\CartAppliedVoucher;

class CartController extends Controller
{
    public function index()
    {
        session()->forget('direct_buy');
        $user = auth()->user();

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        
        $cartItems = $cart->items()->with(['product.seller', 'product'])->get();

        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

        $appliedVoucher = CartAppliedVoucher::where('user_id', $user->id)
                            ->with('voucher')
                            ->first();

        $discountAmount = 0;
        $voucherCode = null;

        if ($appliedVoucher && $appliedVoucher->voucher) {
            if (method_exists($appliedVoucher->voucher, 'calculateDiscount')) {
                $discountAmount = $appliedVoucher->voucher->calculateDiscount($subtotal);
            } else {
                $discountAmount = 0; 
            }
            $voucherCode = $appliedVoucher->voucher->code;
        }

        if ($discountAmount > $subtotal) $discountAmount = $subtotal;

        $finalTotal = $subtotal - $discountAmount;
        $totalItems = $cartItems->sum('quantity');

        $groupedItems = $cartItems->groupBy(function ($item) {
            return $item->product->seller_id;
        });

        $usedVoucherIds = $user->vouchers->pluck('id')->toArray();

        $availableVouchers = Voucher::where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->where('usage_limit', '>', 0)
            ->whereNotIn('id', $usedVoucherIds)
            ->get();

        return view('user.cart', compact(
            'cartItems',
            'groupedItems', 
            'subtotal',
            'discountAmount',
            'finalTotal',
            'voucherCode',
            'totalItems', 
            'availableVouchers'
        ));
    }

    public function updateQuantity(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item = CartItem::findOrFail($id);

        if ($item->cart->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated'
        ]);
    }

    public function destroy($id)
    {
        $item = CartItem::findOrFail($id);

        if ($item->cart->user_id !== auth()->id()) {
            return back()->withErrors('Anda tidak memiliki akses untuk menghapus item ini.');
        }

        $item->delete();

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    public function applyVoucher(Request $request, VoucherService $voucherService)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            $user = auth()->user();
            
            $cart = Cart::where('user_id', $user->id)->first();
            
            if (!$cart || $cart->items->isEmpty()) {
                throw new \Exception("Keranjang belanja kosong.");
            }

            $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);

            $result = $voucherService->applyToCart($request->code, $user, $subtotal);

            return back()->with('success', 'Voucher berhasil dipasang! Hemat: Rp ' . number_format($result['discount_amount']));

        } catch (\Exception $e) {
            return back()->withErrors(['voucher_error' => $e->getMessage()]); 
        }
    }

    public function removeVoucher()
    {
        CartAppliedVoucher::where('user_id', auth()->id())->delete();

        return back()->with('success', 'Voucher berhasil dilepas.');
    }
    public function buyNow(Request $request, $id)
    {
        session()->put('direct_buy', [
            'product_id' => $id,
            'quantity' => $request->quantity ?? 1,
            'variant_id' => $request->variant_id ?? null 
        ]);

        return redirect()->route('checkout.index');
    }
}
