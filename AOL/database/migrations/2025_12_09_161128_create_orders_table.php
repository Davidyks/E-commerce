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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained('seller_details')->onDelete('cascade');
            $table->string('order_code')->unique();

            $table->foreignId('shipping_id')->nullable()->constrained('shippings')->onDelete('set null');
            $table->decimal('shipping_cost', 15,2)->default(0);
            $table->decimal('shipping_insurance_cost', 15,2)->default(0);
            $table->date('estimated_arrival')->nullable();
            
            $table->decimal('subtotal', 15,2);
            $table->decimal('total_price', 15,2);
            $table->decimal('service_fee', 15,2)->default(1000);

            $table->string('payment_method');
            $table->enum('payment_status', ['pending','paid','failed'])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
