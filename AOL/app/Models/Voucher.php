<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    //
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'voucher_user')
                    ->withPivot('used_at');
    }

    public function cartApplied()
    {
        return $this->hasMany(CartAppliedVoucher::class, 'voucher_id');
    }
}
