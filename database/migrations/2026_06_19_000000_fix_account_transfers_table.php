<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_transfers', function (Blueprint $table) {
            $table->string('atvid')->nullable()->after('id');
            $table->date('transfer_date')->after('atvid');
            $table->unsignedBigInteger('from_account_id')->after('transfer_date');
            $table->unsignedBigInteger('to_account_id')->after('from_account_id');
            $table->decimal('amount', 15, 2)->after('to_account_id');
            $table->text('remarks')->nullable()->after('amount');
            $table->unsignedBigInteger('created_by')->nullable()->after('remarks');

            $table->index('transfer_date');
            $table->index('atvid');
            $table->index('from_account_id');
            $table->index('to_account_id');
            $table->index('amount');
        });
    }

    public function down(): void
    {
        Schema::table('account_transfers', function (Blueprint $table) {
            $table->dropColumn(['atvid', 'transfer_date', 'from_account_id', 'to_account_id', 'amount', 'remarks', 'created_by']);
        });
    }
};
