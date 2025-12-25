<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\SellerDetail;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Ambil semua kategori dan seller pertama (pastikan ada seller)
        $categories = Category::all();
        $seller = SellerDetail::first();

        if (!$seller) {
            $this->command->info("Tidak ada seller. Silakan seed dulu SellerDetail.");
            return;
        }

        foreach ($categories as $category) {
            // Tentukan apakah produk ini punya variant atau tidak (acak)
            $hasVariant = $faker->boolean(50); // 50% chance

            $product = Product::create([
                'seller_id' => $seller->id,
                'name' => $category->category_name . " Product",
                'description' => $faker->paragraph,
                'category_id' => $category->id,
                'min_order_qty' => 1,
                'delivery_estimate_days' => $faker->numberBetween(1, 7),
                'rating' => 0,
                'sold_count' => $faker->numberBetween(0, 100),
                'product_image' => 'https://placehold.co/640x480?text=Product'
            ]);

            if ($hasVariant) {
                $variantCount = $faker->numberBetween(2, 3);
                $variantPrices = [];
                $variantStocks = [];

                for ($i = 1; $i <= $variantCount; $i++) {
                    $price = $faker->numberBetween(50, 500);
                    $stock = $faker->numberBetween(5, 50);

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'variant_name' => ($i*50) . "ml",
                        'price' => $price,
                        'stock' => $stock,
                        'image' => 'https://placehold.co/640x480?text=Variant'
                    ]);

                    $variantPrices[] = $price;
                    $variantStocks[] = $stock;
                }

                // Update product dengan harga min/max dan total stock dari variant
                $product->min_price = min($variantPrices);
                $product->max_price = max($variantPrices);
                $product->stock = array_sum($variantStocks); // total stock
                $product->save();
            } else {
                // Kalau tidak ada variant, stock & price langsung di product
                $product->price = $faker->numberBetween(50, 500);
                $product->stock = $faker->numberBetween(10, 100);
                $product->save();
            }
        }
    }
}
