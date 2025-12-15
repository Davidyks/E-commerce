<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function displayProducts(Request $request){
        $categories = Category::all();
        $products = Product::with(['seller', 'category'])
            ->when($request->category, function ($q) use ($request) {
                $q->where('category_id', $request->category);
            })
            ->when($request->q, function ($q) use ($request) {
                $keyword = trim($request->q);

                $q->where(function ($query) use ($keyword) {
                    $query->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%");
                });
            })
            ->when($request->sort, function($q) use ($request){
                match($request->sort){
                    'price_asc' => $q->orderByRaw('COALESCE(price, min_price) ASC'),
                    'price_desc' => $q->orderByRaw('COALESCE(price, max_price) DESC'),
                    'rating_asc' => $q->orderBy('rating', 'asc'),
                    'rating_desc' => $q->orderBy('rating', 'desc'),
                    'sold_count_asc' => $q->orderBy('sold_count', 'asc'),
                    'sold_count_desc' => $q->orderBy('sold_count', 'desc'),
                    'latest_asc' => $q->orderBy('created_at', 'asc'),
                    'latest_desc' => $q->orderBy('created_at', 'desc'),
                    default => null,
                };
            })
            ->when(!$request->sort, fn ($q) =>
                $q->orderBy('created_at', 'desc')
            )
            ->get();

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

    public function productDetail($id)
    {

        $product = Product::with(['variants', 'seller', 'ratings.user', 'category'])->findOrFail($id);

        $ratingCounts = [
            5 => $product->ratings->where('rating', 5)->count(),
            4 => $product->ratings->where('rating', 4)->count(),
            3 => $product->ratings->where('rating', 3)->count(),
            2 => $product->ratings->where('rating', 2)->count(),
            1 => $product->ratings->where('rating', 1)->count(),
        ];

        return view('user.productDetail', compact('product', 'ratingCounts'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
        ], [
            'quantity.min' => 'Minimal pembelian adalah 1 barang.',
            'variant_id.required' => 'Silakan pilih varian terlebih dahulu.',
        ]);

        if ($request->input('action') === 'buy_now') {
            return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan, silakan checkout!');
        } else {
            return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke Keranjang!');
        }
    }

    public function flashsaleDetail(Request $request){
        return view('user.flashsaleDetail');
    }
}
