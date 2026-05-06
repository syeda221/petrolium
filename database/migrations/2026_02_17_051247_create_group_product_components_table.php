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
        Schema::create('group_product_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_product_id')->constrained('group_products')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity_used'); // How many units taken from this product
            $table->decimal('unit_cost', 15, 2); // Cost per unit at time of assembly
            $table->decimal('total_cost', 15, 2); // quantity_used * unit_cost
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_product_components');
    }
};
