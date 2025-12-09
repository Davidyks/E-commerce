<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVoucher extends Model
{
    //
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function storeVoucher()
    {
        return $this->belongsTo(StoreVoucher::class);
    }

    public function globalVoucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function seller()
    {
        return $this->belongsTo(SellerDetail::class);
    }
}
