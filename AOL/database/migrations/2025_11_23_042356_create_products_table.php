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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('seller_details')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            // Harga & stok langsung untuk produk tanpa varian
            $table->decimal('price', 15, 2)->nullable();
            $table->integer('stock')->nullable();

            // Support range harga jika ada variant
            $table->decimal('min_price', 15, 2)->nullable();
            $table->decimal('max_price', 15, 2)->nullable();

            $table->float('rating')->default(0);

            $table->integer('min_order_qty')->default(1);
            $table->integer('delivery_estimate_days')->nullable();

            $table->integer('sold_count')->default(0);
            $table->string('product_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
