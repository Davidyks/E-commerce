<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SellerDetail;
use App\Models\User;
use Illuminate\Support\Str;

class SellerDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Ambil beberapa user untuk dijadikan seller (pastikan ada user di table users)
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info("Tidak ada user. Silakan seed dulu users.");
            return;
        }

        foreach ($users as $user) {
            // Cek apakah user ini sudah punya seller_detail
            if ($user->sellerDetail) {
                continue;
            }

            SellerDetail::create([
                'user_id' => $user->id,
                'store_name' => $faker->company,
                'store_description' => $faker->paragraph,
                'store_logo' => $faker->imageUrl(200, 200, 'business', true),
                'followers' => $faker->numberBetween(0, 5000),
                'total_products' => $faker->numberBetween(0, 100),
                'response_time_hours' => $faker->numberBetween(1, 48),
                'last_active_at' => $faker->dateTimeBetween('-1 week', 'now'),
                'joined_at' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            ]);
        }
    }
}
