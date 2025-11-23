<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerRating extends Model
{
    protected $guarded = ['id'];

    public function seller()
    {
        return $this->belongsTo(SellerDetail::class, 'seller_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

