<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductRating;
use Faker\Factory as Faker;

class ProductRatingSeeder extends Seeder
{
    // php artisan db:seed --class=ProductRatingSeeder

    public function run(): void
    {
        $faker = Faker::create();
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->info('User atau Product belum ada.');
            return;
        }

        foreach ($products as $product) {
            $ratingCount = rand(3, 10);

            foreach ($users->random(min($ratingCount, $users->count())) as $user) {
                ProductRating::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => rand(3, 5),
                    'review' => $faker->optional()->sentence(),
                ]);
            }
        }
    }
}
