<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\FlashSale;
use Faker\Factory as Faker;
use Carbon\Carbon;

class FlashSaleSeeder extends Seeder
{
    // php artisan db:seed --class=FlashSaleSeeder

    public function run(): void
    {
        $faker = Faker::create();

        $products = Product::with('variants')->get();

        foreach ($products as $product) {
            if ($faker->boolean(50)) { // 30% produk masuk flash sale
                $start = Carbon::now();
                $end = (clone $start)->addDays(rand(1,3))->addHours(rand(2, 12));

                if ($product->variants->isNotEmpty()) {
                    // Flash sale per variant
                    foreach ($product->variants as $variant) {
                        $variant_initial_stock = rand(1, $variant->stock);
                        if ($faker->boolean(50)) {
                            FlashSale::create([
                                'product_variant_id' => $variant->id,
                                'start_time' => $start,
                                'end_time' => $end,
                                'flash_price' => $variant->price * 0.7,
                                'flash_stock' => $variant_initial_stock,
                                'initial_stock' => $variant_initial_stock
                            ]);
                        }
                    }
                } else {
                    $product_initial_stock = rand(1, $product->stock);
                    // Flash sale tanpa variant
                    FlashSale::create([
                        'product_id' => $product->id,
                        'start_time' => $start,
                        'end_time' => $end,
                        'flash_price' => $product->price * 0.75,
                        'flash_stock' => $product_initial_stock,
                        'initial_stock' => $product_initial_stock            
                    ]);
                }
            }
        }
    }
}
