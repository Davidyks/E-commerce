<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            // Produk tanpa varian
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');

            // Produk dengan varian
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');

            // Event waktu
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();


            // Informasi harga flash sale
            $table->decimal('flash_price', 15, 2);
            $table->integer('flash_stock')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_sales');
    }
};
