<?php

namespace App\Http\Controllers;

use App\Models\Product as ProductModel;
use App\Models\ProductVariant as ProductVariantModel;
use App\Models\Category as CategoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerProductController extends Controller
{
    /**
     * Show product list of seller.
     */
    public function index()
    {
        $user = Auth::user();
        // Ambil seller detail (aman dari null)
        $seller = $user->sellerDetail;

        // Jika seller belum ada → collection kosong
        $products = $seller
            ? ProductModel::where('seller_id', $seller->id)->get()
            : collect();
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
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:30',
            'category_id' => 'required|exists:categories,id',
            'min_order_qty' => 'required|integer|min:1',
            'delivery_estimate_days' => 'required|integer|min:1',
            'product_image' => 'required|image|max:2048',

            'has_variant' => 'required|boolean',

            // tanpa varian
            'price' => 'nullable|required_if:has_variant,0|numeric|min:0',
            'stock' => 'nullable|required_if:has_variant,0|integer|min:0',


            // dengan varian
            'variants.*.variant_name' => 'required_if:has_variant,1',
            'variants.*.price' => 'required_if:has_variant,1|numeric|min:0',
            'variants.*.stock' => 'required_if:has_variant,1|integer|min:0',
            'variants.*.image' => 'nullable|image|max:2048',
        ]);

        $seller = Auth::user()->sellerDetail;

        // Simpan Gambar Product
        $productImageName = Str::uuid() . '.' . $request->product_image->extension();
        $request->product_image->move(
            public_path('asset/images/product'),
            $productImageName
        );

        $product = ProductModel::create([
            'seller_id' => $seller->id,
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'min_order_qty' => $request->min_order_qty,
            'delivery_estimate_days' => $request->delivery_estimate_days,
            'product_image' => 'asset/images/product/' . $productImageName,
        ]);

        // Jika ada Variant
        if ($request->has_variant) {
            $prices = [];
            $stocks = [];

            foreach ($request->variants as $variant) {
                $variantImagePath = null;

                if (!empty($variant['image'])) {
                    $variantImageName = Str::uuid() . '.' . $variant['image']->extension();
                    $variant['image']->move(
                        public_path('asset/images/variant'),
                        $variantImageName
                    );

                    $variantImagePath = 'asset/images/variant/' . $variantImageName;
                }

                ProductVariantModel::create([
                    'product_id' => $product->id,
                    'variant_name' => $variant['variant_name'],
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                    'image' => $variantImagePath,
                ]);

                $prices[] = $variant['price'];
                $stocks[] = $variant['stock'];
            }

            $product->update([
                'min_price' => min($prices),
                'max_price' => max($prices),
                'stock' => array_sum($stocks),
            ]);
        }
        // tanpa variant
        else {
            $product->update([
                'price' => $request->price,
                'stock' => $request->stock,
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan');
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
