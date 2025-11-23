<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function seller()
    {
        return $this->belongsTo(SellerDetail::class, 'seller_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Produk punya varian atau tidak
     */
    public function getHasVariantAttribute()
    {
        return $this->variants()->exists();
    }

    /**
     * Harga final
     */
    public function getDisplayPriceAttribute()
    {
        if ($this->has_variant) {
            return $this->min_price . ' - ' . $this->max_price;
        }
        return $this->price;
    }

    /**
     * Stock produk jika tanpa varian
     */
    public function getDisplayStockAttribute()
    {
        if ($this->has_variant) {
            return $this->variants()->sum('stock');
        }
        return $this->stock;
    }
}

