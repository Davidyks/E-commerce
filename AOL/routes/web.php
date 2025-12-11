<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function(){
    return view('home');
});

Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::get('/auth/google', [AuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'callback'])->name('google.callback');


Route::get('/register/phone', [AuthController::class, 'registerPhonePage'])->name('register.phonePage');
Route::post('/register/phone', [AuthController::class, 'registerPhone']);

Route::get('/register/password', [AuthController::class, 'registerPasswordPage'])->name('register.passwordPage');
Route::post('/register/password', [AuthController::class, 'registerPassword']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
