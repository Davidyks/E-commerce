<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreVoucher extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(SellerDetail::class);
    }
    public function appliedCarts()
    {
        return $this->hasMany(CartAppliedVoucher::class, 'store_voucher_id');
    }
}