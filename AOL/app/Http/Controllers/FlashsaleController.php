<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FlashsaleController extends Controller
{

    public function createByProduct(Product $product){
        abort_if($product->seller_id !== auth()->id(), 403);

        return view('seller.flashsale.product.create', [
            'product' => $product,
        ]);
    }
    public function createByVariant(ProductVariant $variant){
        abort_if($variant->product->seller_id !== auth()->id(), 403);

        return view('seller.flashsale.variant.create', [
            'variant' => $variant,
        ]);
    }

    public function storeByProduct(Product $product, Request $request){
        $request->validate(
            [
                'start_at' => ['required', 'date', 'after_or_equal:today'],
                'end_at' => ['required', 'date', 'after_or_equal:start_at'],
                'price' => ['required', 'numeric', 'min:1'],
                'stock' => ['required', 'integer', 'min:1', 'max:'.$product->stock],
            ],
            [
                'start_at.required' => __('messages.start_required'),
                'start_at.date' =>__('messages.start_invalid'),
                'start_at.after_or_equal' => __('messages.start_today'),

                'end_at.required' => __('messages.end_required'),
                'end_at.date' => __('messages.end_invalid'),
                'end_at.after_or_equal' => __('messages.end_after_start'),

                'price.required' => __('messages.price_required'),
                'price.numeric' => __('messages.price_numeric'),
                'price.min' => __('messages.price_min'),

                'stock.required' => __('messages.stock_required'),
                'stock.integer' => __('messages.stock_integer'),
                'stock.min' => __('messages.stock_min'),
                'stock.max' => __('messages.stock_exceed'),
            ]
        );

        $request->start_at = Carbon::parse($request->start_at)->startOfDay();
        $request->end_at = Carbon::parse($request->end_at)->endOfDay();

        FlashSale::updateOrCreate([
            'product_id' => $product->id,
            'product_variant_id' => null,
        ],[
            'start_time' => $request->start_at,
            'end_time' => $request->end_at,
            'flash_price' => $request->price,
            'flash_stock' => $request->stock,
            'initial_stock' => $request->stock,
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', __('messages.flashsale_created'));
    }

    public function storeByVariant(ProductVariant $variant, Request $request){
        $request->validate(
            [
                'start_at' => ['required', 'date', 'after_or_equal:today'],
                'end_at' => ['required', 'date', 'after_or_equal:start_at'],
                'price' => ['required', 'numeric', 'min:1'],
                'stock' => ['required', 'integer', 'min:1', 'max:'.$variant->stock],
            ],
            [
                'start_at.required' => __('messages.start_required'),
                'start_at.date' =>__('messages.start_invalid'),
                'start_at.after_or_equal' => __('messages.start_today'),

                'end_at.required' => __('messages.end_required'),
                'end_at.date' => __('messages.end_invalid'),
                'end_at.after_or_equal' => __('messages.end_after_start'),

                'price.required' => __('messages.price_required'),
                'price.numeric' => __('messages.price_numeric'),
                'price.min' => __('messages.price_min'),

                'stock.required' => __('messages.stock_required'),
                'stock.integer' => __('messages.stock_integer'),
                'stock.min' => __('messages.stock_min'),
                'stock.max' => __('messages.stock_exceed'),
            ]
        );

        $request->start_at = Carbon::parse($request->start_at)->startOfDay();
        $request->end_at = Carbon::parse($request->end_at)->endOfDay();

        FlashSale::updateOrCreate([
            'product_id' => null,
            'product_variant_id' => $variant->id,
        ],[
            'start_time' => $request->start_at,
            'end_time' => $request->end_at,
            'flash_price' => $request->price,
            'flash_stock' => $request->stock,
            'initial_stock' => $request->stock,
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', __('messages.flashsale_created'));
    }

    public function edit(Flashsale $flashsale){
        if ($flashsale->product) {
            $product = $flashsale->product;
        } elseif ($flashsale->variant && $flashsale->variant->product) {
            $product = $flashsale->variant->product;
        } else {
            abort(404, 'Flashsale has no valid product relation');
        }

        abort_if($product->seller_id !== auth()->id(), 403);

        return view('seller.flashsale.edit', compact('flashsale', 'product'));
    }

    public function update(Flashsale $flashsale, Request $request){
        $stock = $flashsale->variant ? $flashsale->variant->stock : $flashsale->product->stock;

        $request->validate(
            [
                'start_at' => ['required', 'date', 'after_or_equal:today'],
                'end_at' => ['required', 'date', 'after_or_equal:start_at'],
                'price' => ['required', 'numeric', 'min:1'],
                'stock' => ['required', 'integer', 'min:1', 'max:'.$stock],
            ],
            [
                'start_at.required' => __('messages.start_required'),
                'start_at.date' =>__('messages.start_invalid'),
                'start_at.after_or_equal' => __('messages.start_today'),

                'end_at.required' => __('messages.end_required'),
                'end_at.date' => __('messages.end_invalid'),
                'end_at.after_or_equal' => __('messages.end_after_start'),

                'price.required' => __('messages.price_required'),
                'price.numeric' => __('messages.price_numeric'),
                'price.min' => __('messages.price_min'),

                'stock.required' => __('messages.stock_required'),
                'stock.integer' => __('messages.stock_integer'),
                'stock.min' => __('messages.stock_min'),
                'stock.max' => __('messages.stock_exceed'),
            ]
        );

        $request->start_at = Carbon::parse($request->start_at)->startOfDay();
        $request->end_at = Carbon::parse($request->end_at)->endOfDay();

        if($request->stock > $flashsale->initial_stock){
            $flashsale->update([
                'start_time' => $request->start_at,
                'end_time' => $request->end_at,
                'flash_price' => $request->price,
                'flash_stock' => $request->stock,
                'initial_stock' => $request->stock,
            ]);
        } else {
            $flashsale->update([
                'start_time' => $request->start_at,
                'end_time' => $request->end_at,
                'flash_price' => $request->price,
                'flash_stock' => $request->stock,
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', __('messages.flashsale_updated'));
    }
    
    public function destroy(FlashSale $flashsale){
        if ($flashsale->product) {
            $product = $flashsale->product;
        } elseif ($flashsale->variant && $flashsale->variant->product) {
            $product = $flashsale->variant->product;
        } else {
            abort(404);
        }

        abort_if($product->seller_id !== auth()->id(), 403);

        $flashsale->delete();

        return redirect()
            ->route('products.index')
            ->with('success', __('messages.flashsale_deleted'));
    }

}
