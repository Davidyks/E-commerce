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
     * Show product details.
     */
    public function show(ProductModel $product)
    {
        $this->authorizeAccess($product); // pastikan seller adalah pemilik produk
        $variants = $product->variants;  // ambil semua variant jika ada

        return view('seller.products.show', compact('product', 'variants'));
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
     * Update product + variants.
     */
    public function update(Request $request, ProductModel $product)
    {
        $this->authorizeAccess($product);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'category_id'    => 'required|exists:categories,id',
            'price'          => 'nullable|numeric',
            'stock'          => 'nullable|integer',
            'product_image'  => 'nullable|image|max:2048',

            // Variant fields (form array)
            'variants.*.id'            => 'nullable|exists:product_variants,id',
            'variants.*.variant_name'  => 'nullable|string|max:255',
            'variants.*.price'         => 'nullable|numeric',
            'variants.*.stock'         => 'nullable|integer',
            'variants.*.image'         => 'nullable|image|max:2048',
        ]);

        // Upload main product image jika ada
        if ($request->hasFile('product_image')) {
            // Hapus image lama jika ada
            if ($product->product_image && file_exists(public_path($product->product_image))) {
                unlink(public_path($product->product_image));
            }

            $imagePath = 'img/products/' . time() . '_' . $request->file('product_image')->getClientOriginalName();
            $request->file('product_image')->move(public_path('img/products'), $imagePath);
            $product->product_image = $imagePath;
        }

        // Update main product
        $product->update([
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'] ?? null,
            'stock'       => $validated['stock'] ?? null,
        ]);

        // Handle variants
        $variants = $request->variants;
        $allVariantPrices = [];

        if (!empty($variants)) {
            foreach ($variants as $variantData) {
                if (!isset($variantData['variant_name']) || !$variantData['variant_name']) continue;

                // Update existing variant
                if (isset($variantData['id'])) {
                    $variant = ProductVariantModel::find($variantData['id']);
                    if (!$variant) continue;

                    // Upload new image jika ada
                    if (isset($variantData['image']) && $variantData['image']) {
                        if ($variant->image && file_exists(public_path($variant->image))) {
                            unlink(public_path($variant->image));
                        }
                        $variantImagePath = 'img/variants/' . time() . '_' . $variantData['image']->getClientOriginalName();
                        $variantData['image']->move(public_path('img/variants'), $variantImagePath);
                        $variant->image = $variantImagePath;
                    }

                    $variant->update([
                        'variant_name' => $variantData['variant_name'],
                        'price'        => $variantData['price'],
                        'stock'        => $variantData['stock'],
                    ]);

                    if ($variant->price) $allVariantPrices[] = floatval($variant->price);
                } else {
                    // Create new variant
                    $variantImagePath = null;
                    if (isset($variantData['image']) && $variantData['image']) {
                        $variantImagePath = 'img/variants/' . time() . '_' . $variantData['image']->getClientOriginalName();
                        $variantData['image']->move(public_path('img/variants'), $variantImagePath);
                    }

                    $newVariant = ProductVariantModel::create([
                        'product_id'   => $product->id,
                        'variant_name' => $variantData['variant_name'],
                        'price'        => $variantData['price'],
                        'stock'        => $variantData['stock'],
                        'image'        => $variantImagePath,
                    ]);

                    if ($newVariant->price) $allVariantPrices[] = floatval($newVariant->price);
                }
            }
        }

        // Update min_price & max_price
        if (count($allVariantPrices) > 0) {
            $product->update([
                'min_price' => min($allVariantPrices),
                'max_price' => max($allVariantPrices),
            ]);
        } else {
            if ($product->price) {
                $product->update([
                    'min_price' => $product->price,
                    'max_price' => $product->price,
                ]);
            }
        }

        return redirect()->route('seller.products.index')->with('success', 'Product updated successfully.');
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
