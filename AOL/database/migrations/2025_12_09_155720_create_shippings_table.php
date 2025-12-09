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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->string('courier');      // misal: JNE, J&T, Sicepat
            $table->string('service');      // misal: Reguler, Cargo, YES
            $table->decimal('base_cost', 15, 2); // ongkir dasar
            $table->integer('estimated_days')->nullable(); // estimasi tiba
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};
