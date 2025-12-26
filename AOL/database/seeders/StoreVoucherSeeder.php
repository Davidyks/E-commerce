<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StoreVoucher;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreVoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellerIds = Product::select('seller_id')->distinct()->pluck('seller_id');

        foreach($sellerIds as $sellerId){
            StoreVoucher::firstOrCreate([
                'seller_id' => $sellerId,
                'title' => 'Discount 10%'
            ],[
                'discount_percent' => 10,
                'max_discount' => 50,
                'min_purchase' => 300,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addDays(30),
            ]);

            StoreVoucher::firstOrCreate([
                'seller_id' => $sellerId,
                'title' => 'Discount Special 30%'
            ],[
                'discount_percent' => 30,
                'max_discount' => 250,
                'min_purchase' => 500,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addDays(30),
            ]);

            StoreVoucher::firstOrCreate([
                'seller_id' => $sellerId,
                'title' => 'Discount 50%'
            ],[
                'discount_percent' => 50,
                'max_discount' => 50,
                'min_purchase' => 70,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addDays(30),
            ]);
        }
    }
}
