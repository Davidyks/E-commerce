<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register/phone', [AuthController::class, 'registerPhonePage'])->name('register.phonePage');
Route::post('/register/phone', [AuthController::class, 'registerPhone']);

Route::get('/register/password', [AuthController::class, 'registerPasswordPage'])->name('register.passwordPage');
Route::post('/register/password', [AuthController::class, 'registerPassword']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
