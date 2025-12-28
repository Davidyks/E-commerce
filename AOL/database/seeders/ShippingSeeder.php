<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('shippings')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('shippings')->insert([
            [
                'courier' => 'JNE Express',
                'service' => 'REG (Reguler)',
                'base_cost' => 16,
                'estimated_days' => 3,
                'created_at' => now(), 
                'updated_at' => now(),
            ],
            [
                'courier' => 'SiCepat',
                'service' => 'GOKIL (Kargo)',
                'base_cost' => 20,
                'estimated_days' => 2, 
                'created_at' => now(), 
                'updated_at' => now(),
            ],
            [
                'courier' => 'GoSend',
                'service' => 'Instant Delivery',
                'base_cost' => 35,
                'estimated_days' => 0,
                'created_at' => now(), 
                'updated_at' => now(),
            ],
            [
                'courier' => 'J&T Express',
                'service' => 'EZ',
                'base_cost' => 18,
                'estimated_days' => 3, 
                'created_at' => now(), 
                'updated_at' => now(),
            ],
            [
                'courier' => 'Ninja Xpress',
                'service' => 'Standard',
                'base_cost' => 15,
                'estimated_days' => 4, 
                'created_at' => now(), 
                'updated_at' => now(),
            ],
        ]);
    }
}