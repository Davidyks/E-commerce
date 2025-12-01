<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'store_description',
        'store_logo',
        'followers',
        'total_products',
        'rating',
        'response_time_hours',
        'joined_at',
        'last_active_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function ratings()
    {
        return $this->hasMany(SellerRating::class, 'seller_id');
    }

    public function storeVouchers()
    {
        return $this->hasMany(StoreVoucher::class, 'seller_id');
    }

    // Accessor rating average dan jumlah ulasan
    public function getRatingAverageAttribute()
    {
        return round($this->ratings()->avg('rating'), 1) ?? 0;
    }

    public function getRatingCountAttribute()
    {
        return $this->ratings()->count();
    }
}
