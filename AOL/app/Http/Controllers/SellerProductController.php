<?php

namespace App\Http\Controllers;

use App\Models\Product as ProductModel;
use App\Models\Category as CategoryModel;
use App\Models\ProductVariant as ProductVariantModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerProductController extends Controller
{
    /**
     * Show product list of seller.
     */
    public function index()
    {
        $products = ProductModel::where('seller_id', Auth::user()->sellerDetail->id)->get();
        return view('seller.products.index', compact('products'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $categories = CategoryModel::all();  // untuk dropdown
        return view('seller.products.create', compact('categories'));
    }

    /**
     * Store product + variants.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'category_id'    => 'required|exists:categories,id',
            'price'          => 'nullable|numeric',
            'stock'          => 'nullable|integer',
            'product_image'  => 'nullable|image|max:2048',

            // Variant fields (form array)
            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.price'        => 'nullable|numeric',
            'variants.*.stock'        => 'nullable|integer',
            'variants.*.image'        => 'nullable|image|max:2048',
        ]);

        // Upload main product image
        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $imagePath = 'img/products/' . time() . '_' . $request->file('product_image')->getClientOriginalName();
            $request->file('product_image')->move(public_path('img/products'), $imagePath);
        }

        // Create main product
        $product = ProductModel::create([
            'seller_id'   => Auth::user()->sellerDetail->id,
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'] ?? null,
            'stock'       => $validated['stock'] ?? null,
            'product_image' => $imagePath,
        ]);

        // Handle variants
        $variants = $request->variants;

        $allVariantPrices = [];

        if (!empty($variants)) {
            foreach ($variants as $variant) {

                if (!isset($variant['variant_name']) || !$variant['variant_name']) continue;

                // Upload variant image
                $variantImagePath = null;
                if (isset($variant['image']) && $variant['image']) {
                    $variantImagePath = 'img/variants/' . time() . '_' . $variant['image']->getClientOriginalName();
                    $variant['image']->move(public_path('img/variants'), $variantImagePath);
                }

                // Create variant
                ProductVariantModel::create([
                    'product_id'   => $product->id,
                    'variant_name' => $variant['variant_name'],
                    'price'        => $variant['price'],
                    'stock'        => $variant['stock'],
                    'image'        => $variantImagePath,
                ]);

                $allVariantPrices[] = floatval($variant['price']);
            }
        }

        // Jika ada varian → hitung min_price & max_price
        if (count($allVariantPrices) > 0) {
            $product->update([
                'min_price' => min($allVariantPrices),
                'max_price' => max($allVariantPrices),
            ]);
        } else {
            // Jika TIDAK ada variant → min_price & max_price = price
            if ($product->price) {
                $product->update([
                    'min_price' => $product->price,
                    'max_price' => $product->price,
                ]);
            }
        }

        return redirect()->route('seller.products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Edit Form
     */
    public function edit(ProductModel $product)
    {
        $this->authorizeAccess($product);

        $categories = CategoryModel::all();
        $variants = $product->variants;

        return view('seller.products.edit', compact('product', 'categories', 'variants'));
    }

    /**
     * Delete product.
     */
    public function destroy(ProductModel $product)
    {
        $this->authorizeAccess($product);

        $product->delete();

        return redirect()->route('seller.products.index')->with('success', 'Product deleted.');
    }

    /**
     * Ensure seller is the owner.
     */
    private function authorizeAccess(ProductModel $product)
    {
        if ($product->seller_id !== Auth::user()->sellerDetail->id) {
            abort(403);
        }
    }
}
