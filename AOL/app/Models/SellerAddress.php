<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerAddress extends Model
{
    //
    protected $guarded = [];
    public function seller() {
        return $this->belongsTo(SellerDetail::class);
    }
}
