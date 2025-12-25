<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function seller()
    {
        return $this->belongsTo(SellerDetail::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function flashSales()
    {
        return $this->hasMany(FlashSale::class);
    }


    // Accessor utk rating ui
    public function getRatingAverageAttribute()
    {
        return round($this->ratings()->avg('rating'), 2) ?? 0;
    }

    public function getRatingCountAttribute()
    {
        return $this->ratings()->count();
    }

    // Harga utama (jika ada varian pakai min-max price)
    public function getDisplayPriceAttribute()
    {
        if ($this->min_price && $this->max_price) {
            return "{$this->min_price} - {$this->max_price}";
        }
        return $this->price ?: '-';
    }
}