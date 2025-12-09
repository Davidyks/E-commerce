<?php

namespace App\Models;
use App\Models\CartItem;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function appliedVouchers()
    {
        return $this->hasMany(CartAppliedVoucher::class);
    }
}
