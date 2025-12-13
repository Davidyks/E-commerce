<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SellerProductController;
use App\Http\Controllers\CartController;

Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::get('/auth/google', [AuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'callback'])->name('google.callback');

Route::get('/register', [AuthController::class, 'registerPage'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [HomeController::class, 'showBeforeLogin'])->name('show.beforelogin');

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'showProducts'])->name('home');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('seller/products', SellerProductController::class);
    Route::get('/start-selling', [SellerController::class, 'startSelling'])->name('start.selling');
    Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/voucher/apply', [CartController::class, 'applyVoucher'])->name('cart.voucher.apply');
    Route::delete('/cart/voucher/remove', [CartController::class, 'removeVoucher'])->name('cart.voucher.remove');
    Route::get('/seller/home', [SellerController::class, 'index'])->name('seller.home');
});
