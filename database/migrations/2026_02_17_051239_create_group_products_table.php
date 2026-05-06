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
        Schema::create('group_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->integer('quantity_produced'); // Number of bags/units created
            $table->decimal('total_cost', 15, 2); // Total cost from components
            $table->decimal('cost_per_unit', 15, 2); // Cost per bag/unit
            $table->decimal('sale_price', 15, 2); // Sale price per unit
            $table->integer('current_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_products');
    }
};
