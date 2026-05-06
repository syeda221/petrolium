<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_closings', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('total_in', 15, 2)->default(0);
            $table->decimal('total_out', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->boolean('is_closed')->default(false);
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_closings');
    }
};
