<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerDetail extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->hasMany(SellerRating::class, 'seller_id');
    }

    public function getFormattedResponseTimeAttribute()
    {
        return $this->response_time_hours . ' hour count';
    }
}
