<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'discount_value' => 'float',
        'min_purchase' => 'float',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'voucher_user')
                    ->withPivot('used_at')
                    ->withTimestamps(); 
    }

    public function cartApplied()
    {
        return $this->hasMany(CartAppliedVoucher::class, 'voucher_id');
    }

    public function isValid(?User $user, float $purchaseAmount): bool
    {
        $now = now();

        if ($this->start_at && $now->lt($this->start_at)) return false;
        if ($this->end_at && $now->gt($this->end_at)) return false;

        if ($purchaseAmount < $this->min_purchase) return false;

        if ($this->usage_limit) {
            $totalUsed = $this->users()->count();
            if ($totalUsed >= $this->usage_limit) return false;
        }

        if ($user && $this->per_user_limit) {
            $userUsage = $this->users()->where('user_id', $user->id)->count();
            
            if ($userUsage >= $this->per_user_limit) return false;
        }

        return true;
    }
}
