<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            SellerDetailSeeder::class,
            ProductSeeder::class,
            FlashSaleSeeder::class,
            VoucherSeeder::class,
            StoreVoucherSeeder::class,
            ProductRatingSeeder::class,
            ShippingSeeder::class,
        ]);

        Product::with('ratings')->get()->each(function ($product) {
            $avg = $product->ratings()
                ->whereNotNull('rating')
                ->avg('rating');

            $product->updateQuietly([
                'rating' => $avg ?? 0,
            ]);
        });
    }
}
