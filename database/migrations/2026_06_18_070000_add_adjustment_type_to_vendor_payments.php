<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdjustmentTypeToVendorPayments extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vendor_payments')) {
            return;
        }
        Schema::table('vendor_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_payments', 'adjustment_type')) {
                $table->enum('adjustment_type', ['plus', 'minus'])->default('minus')->after('amount');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('vendor_payments')) {
            return;
        }
        Schema::table('vendor_payments', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_payments', 'adjustment_type')) {
                $table->dropColumn('adjustment_type');
            }
        });
    }
}
