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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('remarks');
        });
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('amount');
        });
        Schema::table('vendor_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('amount');
        });
        Schema::table('other_incomes', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
        Schema::table('vendor_payments', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
        Schema::table('other_incomes', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }
};
