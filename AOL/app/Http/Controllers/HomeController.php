<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function showBeforeLogin(){
        $categories = Category::all();
        $flashsales = FlashSale::where('start_time', '<=', now())
                    ->where('end_time', '>', now())
                    ->with(['product', 'variant'])
                    ->get();
        $topProducts = Product::with('seller')
                ->orderByDesc('sold_count')
                ->take(5)
                ->get();

        return view('layout.sebelum_login.home', compact('categories', 'flashsales', 'topProducts'));
    }

    public function showProducts(){
        $categories = Category::all();
        $flashsales = FlashSale::where('start_time', '<=', now())
                    ->where('end_time', '>', now())
                    ->with(['product', 'variant'])
                    ->get();
        $topProducts = Product::with('seller')
                ->orderByDesc('sold_count')
                ->take(5)
                ->get();

        return view('user.dashboard', compact('categories', 'flashsales', 'topProducts'));
    }
}
