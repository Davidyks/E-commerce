<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang tidak boleh di-mass assign
     * Sebaiknya gunakan guarded untuk keamanan
     */
    protected $guarded = ['id'];

    /**
     * Kolom yang disembunyikan saat mengambil data user
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting otomatis
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 10+ auto hash
    ];

    /**
     * Jika ingin dukung Laravel < 10 untuk hash manual
     * maka tetap gunakan setter seperti di bawah
     * (tidak wajib jika pakai 'hashed' cast)
     */
    public function setPasswordAttribute($password)
    {
        if ($password) {
            $this->attributes['password'] = Hash::make($password);
        }
    }


    /**
     * Contoh relasi jika user juga bisa jadi seller
     * Nanti bisa digunakan seperti: $user->sellerDetail
     */
    public function sellerDetail()
    {
        return $this->hasOne(SellerDetail::class);
    }

    public function productRatings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'voucher_user')
                    ->withPivot('used_at');
    }
}
