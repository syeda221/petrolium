<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdjustmentTypeToCustomerPayments extends Migration
{
    public function up()
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_payments', 'adjustment_type')) {
                $table->enum('adjustment_type', ['plus', 'minus'])->default('minus')->after('amount');
            }
        });
    }

    public function down()
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            if (Schema::hasColumn('customer_payments', 'adjustment_type')) {
                $table->dropColumn('adjustment_type');
            }
        });
    }
}
