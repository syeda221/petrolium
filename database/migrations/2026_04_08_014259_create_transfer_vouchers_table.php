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
        Schema::create('transfer_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('tvid')->unique();
            $table->date('transfer_date');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('vendor_id');
            $table->decimal('amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_vouchers');
    }
};
