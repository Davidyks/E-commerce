<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function displayProducts(){
        $products = Product::with(['seller', 'category'])->get();
        $categories = Category::all();

        return view('user.products', compact('products', 'categories'));
    }

    public function displayFlashsales(){
        $flashsales = FlashSale::where('start_time', '<=', now())
                    ->where('end_time', '>', now())
                    ->with(['product', 'variant'])
                    ->get();
        $categories = Category::all();

        return view('user.flashsales', compact('flashsales', 'categories'));
    }

    public function productDetail(Request $request){
        return view('user.productDetail');
    }

    public function flashsaleDetail(Request $request){
        return view('user.flashsaleDetail');
    }
}
