<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\User;
use App\Models\CartAppliedVoucher;
use Exception;

class VoucherService
{
    public function applyToCart(string $code, User $user, float $totalBelanja)
    {
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            throw new Exception("Kode voucher tidak ditemukan.");
        }

        if (!$voucher->isValid($user, $totalBelanja)) {
            throw new Exception("Voucher tidak memenuhi syarat atau kuota habis.");
        }
    
        $existing = CartAppliedVoucher::where('user_id', $user->id)->first();
        if ($existing) {
             $existing->delete(); 
        }

        CartAppliedVoucher::create([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id
        ]);

        return [
            'voucher' => $voucher,
            'discount_amount' => $voucher->calculateDiscount($totalBelanja)
        ];
    }

    public function markAsUsed(User $user)
    {
        $applied = CartAppliedVoucher::where('user_id', $user->id)->first();

        if ($applied) {
            $user->vouchers()->attach($applied->voucher_id, [
                'used_at' => now()
            ]);

            $applied->delete();
        }
    }
}