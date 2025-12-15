<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Voucher;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Voucher::firstOrCreate(
            ['code' => 'HEMAT10RB'], 
            [
                'title' => 'Potongan 10 Ribu Spesial',
                'discount_type' => 'fixed',
                'discount_value' => 10000,
                'min_purchase' => 50000,
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'start_at' => now(),
                'end_at' => now()->addMonth(),
            ]
        );

        Voucher::firstOrCreate(
            ['code' => 'DISKON50'], 
            [
                'title' => 'Diskon 50 Persen',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'max_discount' => 20000,
                'min_purchase' => 0,
                'usage_limit' => 50,
                'per_user_limit' => 2,
                'start_at' => now(),
                'end_at' => now()->addDays(7),
            ]
        );
    }
}
