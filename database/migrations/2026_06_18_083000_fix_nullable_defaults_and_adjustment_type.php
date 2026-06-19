<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // customer_ledgers: fix previous_balance, closing_balance default
        DB::statement('ALTER TABLE customer_ledgers MODIFY previous_balance DECIMAL(12,2) NULL DEFAULT 0');
        DB::statement('ALTER TABLE customer_ledgers MODIFY closing_balance DECIMAL(12,2) NULL DEFAULT 0');

        // vendor_ledgers: fix nullable on text columns
        DB::statement('ALTER TABLE vendor_ledgers MODIFY opening_balance TEXT NULL');
        DB::statement('ALTER TABLE vendor_ledgers MODIFY previous_balance TEXT NULL');
        DB::statement('ALTER TABLE vendor_ledgers MODIFY closing_balance TEXT NULL');

        // vendor_payments: change adjustment_type from enum to varchar
        DB::statement("ALTER TABLE vendor_payments MODIFY adjustment_type VARCHAR(255) DEFAULT 'minus'");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE customer_ledgers MODIFY previous_balance DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE customer_ledgers MODIFY closing_balance DECIMAL(12,2) NOT NULL');

        DB::statement('ALTER TABLE vendor_ledgers MODIFY opening_balance TEXT NOT NULL');
        DB::statement('ALTER TABLE vendor_ledgers MODIFY previous_balance TEXT NOT NULL');
        DB::statement('ALTER TABLE vendor_ledgers MODIFY closing_balance TEXT NOT NULL');

        DB::statement("ALTER TABLE vendor_payments MODIFY adjustment_type ENUM('plus','minus') DEFAULT 'minus'");
    }
};
