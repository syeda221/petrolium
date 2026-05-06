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
        Schema::table('transfer_vouchers', function (Blueprint $table) {
            $table->string('source_party_type')->nullable()->after('transfer_date'); // 'customer' or 'vendor'
            $table->bigInteger('source_party_id')->nullable()->after('source_party_type');
            $table->string('destination_party_type')->nullable()->after('source_party_id'); // 'customer' or 'vendor'
            $table->bigInteger('destination_party_id')->nullable()->after('destination_party_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_vouchers', function (Blueprint $table) {
            $table->dropColumn(['source_party_type', 'source_party_id', 'destination_party_type', 'destination_party_id']);
        });
    }
};
