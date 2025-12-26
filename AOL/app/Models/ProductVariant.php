<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function activeFlashsale()
    {
        return $this->hasOne(FlashSale::class)
            ->where('start_time', "<=", now())
            ->where('end_time', ">=", now())
            ->where('flash_stock', '>', 0);
    }

}
