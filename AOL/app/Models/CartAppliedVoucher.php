<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartAppliedVoucher extends Model
{
    //
    protected $guarded = [];
    
    public function seller()
    {
        return $this->belongsTo(SellerDetail::class);
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function storeVoucher()
    {
        return $this->belongsTo(StoreVoucher::class);
    }

    public function globalVoucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
