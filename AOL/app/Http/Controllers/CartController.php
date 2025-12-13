<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Services\VoucherService;
use App\Models\CartAppliedVoucher;

class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cartItems = $user->cartItems()->with(['product.seller'])->get();

        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

        $appliedVoucher = \App\Models\CartAppliedVoucher::where('user_id', $user->id)
                            ->with('voucher')
                            ->first();

        $discountAmount = 0;
        $voucherCode = null;

        if ($appliedVoucher && $appliedVoucher->voucher) {
            $discountAmount = $appliedVoucher->voucher->calculateDiscount($subtotal);
            $voucherCode = $appliedVoucher->voucher->code;
        }

        $finalTotal = $subtotal - $discountAmount;
        $totalItems = $cartItems->count();

        $groupedItems = $cartItems->groupBy(function ($item) {
            return $item->product->seller_id;
        });
        $availableVouchers = \App\Models\Voucher::where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->where('usage_limit', '>', 0)
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

    public function applyVoucher(Request $request, VoucherService $voucherService)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            $user = auth()->user();
            
            $cartItems = $user->cartItems; 
            $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

            $result = $voucherService->applyToCart($request->code, $user, $subtotal);

            return back()->with('success', 'Voucher berhasil dipasang! Hemat: Rp ' . number_format($result['discount_amount']));

        } catch (\Exception $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }
    }
    public function removeVoucher()
    {
        CartAppliedVoucher::where('user_id', auth()->id())->delete();

        return back()->with('success', 'Voucher berhasil dilepas.');
    }
    
}
