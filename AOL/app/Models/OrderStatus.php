<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    //
    public $timestamps = false; // pakai changed_at
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
