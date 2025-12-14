<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashSale;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function showBeforeLogin(){
        $categories = Category::all();
        $flashsales = FlashSale::where('start_time', '<=', now())
                    ->where('end_time', '>', now())
                    ->with(['product', 'variant'])
                    ->get();

        return view('layout.sebelum_login.home', compact('categories', 'flashsales'));
    }

    public function showProducts(){
        $categories = Category::all();
        $flashsales = FlashSale::where('start_time', '<=', now())
                    ->where('end_time', '>', now())
                    ->with(['product', 'variant'])
                    ->get();

        return view('user.dashboard', compact('categories', 'flashsales'));
    }
}
