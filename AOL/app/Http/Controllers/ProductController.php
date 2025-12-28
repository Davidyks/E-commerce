<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\ProductVariant;
use App\Models\SellerDetail;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function displayFlashsales(Request $request){
        $categories = Category::all();
        $flashsales = FlashSale::where('start_time', '<=', now())
                    ->where('end_time', '>', now())
                    ->with(['product', 'variant'])
                    ->when($request->q, function ($q) use ($request) {
                        $keyword = trim($request->q);

                        $q->where(function ($query) use ($keyword) {
                            $query->whereHas('product', function ($q) use ($keyword) {
                                $q->where('name', 'LIKE', "%{$keyword}%")
                                    ->orWhere('description', 'LIKE', "%{$keyword}%");
                            })
                            ->orWhereHas('variant.product', function ($q) use ($keyword) {
                                $q->where('name', 'LIKE', "%{$keyword}%")
                                    ->orWhere('description', 'LIKE', "%{$keyword}%");
                            });
                        });
                    })
                    ->when($request->category, function ($q) use ($request) {
                        $q->where(function ($query) use ($request) {

                            $query->whereHas('product', function ($q) use ($request) {
                                $q->where('category_id', $request->category);
                            })
                            ->orWhereHas('variant.product', function ($q) use ($request) {
                                $q->where('category_id', $request->category);
                            });

                        });
                    })
                    ->get();

        return view('user.flashsales', compact('flashsales', 'categories'));
    }

    public function productDetail($id)
    {
        $user = Auth::user();
        $product = Product::with(['activeFlashsale','variants.activeFlashsale', 'ratings.user', 'category'])->findOrFail($id);
        $seller = SellerDetail::where('id', $product->seller_id)->with(['storeVouchers'])->first();
        $sellerProducts = Product::where('seller_id', $seller->id)->count();
        $sellerRating = Product::where('seller_id', $seller->id)->average('rating');
        $sellerJoined = $this->sellerJoinedLabel($seller->created_at);
        $userReview = ProductRating::where('product_id', $product->id)->orderByDesc('updated_at')->get();
        $ownedReview = ProductRating::where('product_id', $product->id)->where('user_id', $user->id)->first();

        $ratingCounts = [
            5 => $product->ratings->where('rating', 5)->count(),
            4 => $product->ratings->where('rating', 4)->count(),
            3 => $product->ratings->where('rating', 3)->count(),
            2 => $product->ratings->where('rating', 2)->count(),
            1 => $product->ratings->where('rating', 1)->count(),
        ];

        return view('user.productDetail', compact('product', 'ratingCounts', 'ownedReview' , 'userReview', 'seller', 'sellerProducts', 'sellerRating', 'sellerJoined', 'user'));
    }

    private function sellerJoinedLabel($createdAt)
    {
        $createdAt = Carbon::parse($createdAt);
        $now = Carbon::now();

        $days = $createdAt->diffInDays($now);

        if ($days < 1) {
            return '1 day';
        }

        if ($days < 30) {
            return $days . ' days';
        }

        $months = $createdAt->diffInMonths($now);
        if ($months < 12) {
            return $months . ' months';
        }

        $years = $createdAt->diffInYears($now);
        return $years . ' years';
    }

    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $request->validate([
            'quantity' => 'required|numeric|min:'.$product->min_order_qty,
            'product_id' => 'required|exists:products,id',
            'variant_id' => [
                'nullable',
                Rule::exists('product_variants','id')->where('product_id', $product->id)
            ],
        ], [
            'quantity.min' => 'Purchase at least :min item(s).',
        ]);

        if ($product->variants()->exists() && !$request->filled('variant_id')) {
            return back()
                ->withErrors(['variant_id' => 'Please select a variant first.'])
                ->withInput();
        }

        if ($request->input('action') === 'buy_now') {
            return redirect()->route('checkout.index')->with('success', 'Please proceed with your payment!');
        } else {
            $user = Auth::user();
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            $variant = null;
            $unitPrice = $product->price;

            if($request->filled('variant_id')){
                $variant = ProductVariant::where('id', $request->variant_id)
                    ->where('product_id', $product->id)
                    ->firstOrFail();

                $unitPrice = $variant->price;
            }

            $flashSale = FlashSale::where('product_id', $product->id)
                ->where('start_time', '<=', now())
                ->where('end_time', '>', now())
                ->first();

            if ($flashSale && $flashSale->flash_stock > 0) {
                $unitPrice = $flashSale->flash_price;
            }

            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $request->variant_id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $request->quantity;
                $existingItem->price = $unitPrice;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'product_variant_id' => $request->variant_id,
                    'seller_id' => $product->seller_id,
                    'quantity' => $request->quantity,
                    'price' => $unitPrice,
                ]);
            }

            return redirect()->back()->with('success', 'Product added to cart successfully!');
        }
    }

    public function storeRating(Request $request, Product $product){
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        ProductRating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ],
            [
                'rating' => $request->rating ?? 1,
                'review' => $request->review,
                'updated_at' => Carbon::now(),
            ]
        );

        return back()->with('success', 'Review submitted!');
    }

    public function destroyRating(ProductRating $review){
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
