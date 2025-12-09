<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(SellerDetail::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function vouchers()
    {
        return $this->hasMany(OrderVoucher::class);
    }

    public function statuses()
    {
        return $this->hasMany(OrderStatus::class);
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class);
    }
}
