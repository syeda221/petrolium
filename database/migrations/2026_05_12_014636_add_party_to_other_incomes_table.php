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
        Schema::table('other_incomes', function (Blueprint $table) {
            $table->string('party_type')->default('account')->after('amount');
            $table->unsignedBigInteger('vendor_id')->nullable()->after('party_type');
            $table->unsignedBigInteger('customer_id')->nullable()->after('vendor_id');
            $table->unsignedBigInteger('account_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('other_incomes', function (Blueprint $table) {
            $table->dropColumn(['party_type', 'vendor_id', 'customer_id']);
        });
    }
};
