<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\Product as ProductModel;
use App\Models\ProductVariant as ProductVariantModel;
use App\Models\Category as CategoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
            ? ProductModel::with('variants', 'activeFlashsale')
            ->where('seller_id', $seller->id)
            ->latest()
            ->paginate(10)
            : ProductModel::whereRaw('1 = 0')->paginate(10);


        $flashsales = FlashSale::where('end_time', '>=', now())
                            ->where('flash_stock', '>', '0')
                            ->get();
        return view('seller.products.index', compact('products', 'flashsales'));
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
    public function update(Request $request, $id)
    {
        $product = ProductModel::with('variants')->findOrFail($id);

        // DETEKSI ADA VARIANT ATAU TIDAK
        $hasVariant = $request->has('variants') && count($request->variants) > 0;

        // VALIDASI (DISAMAKAN DENGAN STORE)
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:30',
            'category_id' => 'required|exists:categories,id',
            'min_order_qty' => 'required|integer|min:1',
            'delivery_estimate_days' => 'required|integer|min:1',
            'product_image' => 'nullable|image|max:2048',

            // tanpa varian
            'price' => $hasVariant ? 'nullable' : 'required|numeric|min:0',
            'stock' => $hasVariant ? 'nullable' : 'required|integer|min:0',

            // dengan varian
            'variants.*.variant_name' => $hasVariant ? 'required' : 'nullable',
            'variants.*.price' => $hasVariant ? 'required|numeric|min:0' : 'nullable',
            'variants.*.stock' => $hasVariant ? 'required|integer|min:0' : 'nullable',
            'variants.*.image' => 'nullable|image|max:2048',
        ]);

        // Update Gambar Produk
        if ($request->hasFile('product_image')) {
            if ($product->product_image && File::exists(public_path($product->product_image))) {
                File::delete(public_path($product->product_image));
            }

            $productImageName = Str::uuid() . '.' . $request->product_image->extension();
            $request->product_image->move(
                public_path('asset/images/product'),
                $productImageName
            );

            $product->product_image = 'asset/images/product/' . $productImageName;
        }

        // Update Data Produk
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'min_order_qty' => $request->min_order_qty,
            'delivery_estimate_days' => $request->delivery_estimate_days,
        ]);

        // Jika Ada Variant
        if ($hasVariant) {

            $existingVariantIds = $product->variants->pluck('id')->toArray();
            $requestVariantIds = collect($request->variants)
                                    ->pluck('id')
                                    ->filter()
                                    ->toArray();

            // HAPUS VARIANT YANG DIHAPUS DARI FORM
            $deletedVariants = array_diff($existingVariantIds, $requestVariantIds);
            foreach ($deletedVariants as $variantId) {
                $variant = ProductVariantModel::find($variantId);
                if ($variant) {
                    if ($variant->image && File::exists(public_path($variant->image))) {
                        File::delete(public_path($variant->image));
                    }
                    $variant->delete();
                }
            }

            $prices = [];
            $stocks = [];

            // UPDATE / TAMBAH VARIANT
            foreach ($request->variants as $variantData) {

                // UPDATE VARIANT
                if (!empty($variantData['id'])) {
                    $variant = ProductVariantModel::find($variantData['id']);

                    if (!$variant) continue;

                    if (!empty($variantData['image'])) {
                        if ($variant->image && File::exists(public_path($variant->image))) {
                            File::delete(public_path($variant->image));
                        }

                        $variantImageName = Str::uuid() . '.' . $variantData['image']->extension();
                        $variantData['image']->move(
                            public_path('asset/images/variant'),
                            $variantImageName
                        );

                        $variant->image = 'asset/images/variant/' . $variantImageName;
                    }

                    $variant->update([
                        'variant_name' => $variantData['variant_name'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                    ]);
                }
                // TAMBAH VARIANT BARU
                else {
                    $variantImagePath = null;

                    if (!empty($variantData['image'])) {
                        $variantImageName = Str::uuid() . '.' . $variantData['image']->extension();
                        $variantData['image']->move(
                            public_path('asset/images/variant'),
                            $variantImageName
                        );

                        $variantImagePath = 'asset/images/variant/' . $variantImageName;
                    }

                    ProductVariantModel::create([
                        'product_id' => $product->id,
                        'variant_name' => $variantData['variant_name'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'image' => $variantImagePath,
                    ]);
                }

                $prices[] = $variantData['price'];
                $stocks[] = $variantData['stock'];
            }

            // UPDATE AGREGAT PRODUK
            $product->update([
                'price' => null,
                'min_price' => min($prices),
                'max_price' => max($prices),
                'stock' => array_sum($stocks),
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui');
    }

    /**
     * Delete product.
     */
    public function destroy(ProductModel $product)
    {
        $product->load('variants');

        // Hapus Gambar Variant
        foreach ($product->variants as $variant) {
            if ($variant->image && File::exists(public_path($variant->image))) {
                File::delete(public_path($variant->image));
            }
        }

        // Hapus Gambar Produk
        if ($product->product_image && File::exists(public_path($product->product_image))) {
            File::delete(public_path($product->product_image));
        }

        // Hapus Data Variant
        $product->variants()->delete();

        // Hapus Data Product
        $product->delete();

        return redirect()
            ->route('products.index', ['page' => request('page')])
            ->with('success', 'Produk berhasil dihapus');
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
