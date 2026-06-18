<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('account_heads')) {
            Schema::create('account_heads', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('type')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('head_id');
                $table->string('account_code')->unique();
                $table->string('title');
                $table->enum('type', ['Debit', 'Credit']);
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->boolean('status')->default(1);
                $table->timestamps();

                $table->index('title');
            });
        }

        if (!Schema::hasTable('receipt_vouchers')) {
            Schema::create('receipt_vouchers', function (Blueprint $table) {
                $table->id();
                $table->text('rvid')->nullable();
                $table->text('receipt_date')->nullable();
                $table->text('entry_date')->nullable();
                $table->text('type')->nullable();
                $table->text('party_id')->nullable();
                $table->text('tel')->nullable();
                $table->text('remarks')->nullable();
                $table->text('narration_id')->nullable();
                $table->text('reference_no')->nullable();
                $table->text('row_account_head')->nullable();
                $table->text('row_account_id')->nullable();
                $table->text('discount_value')->nullable();
                $table->text('kg')->nullable();
                $table->text('rate')->nullable();
                $table->text('amount')->nullable();
                $table->text('total_amount')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_vouchers')) {
            Schema::create('payment_vouchers', function (Blueprint $table) {
                $table->id();
                $table->text('pvid')->nullable();
                $table->text('receipt_date')->nullable();
                $table->text('entry_date')->nullable();
                $table->text('type')->nullable();
                $table->text('party_id')->nullable();
                $table->text('tel')->nullable();
                $table->text('remarks')->nullable();
                $table->text('narration_id')->nullable();
                $table->text('reference_no')->nullable();
                $table->text('row_account_head')->nullable();
                $table->text('row_account_id')->nullable();
                $table->text('discount_value')->nullable();
                $table->text('kg')->nullable();
                $table->text('rate')->nullable();
                $table->text('amount')->nullable();
                $table->text('total_amount')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('expense_vouchers')) {
            Schema::create('expense_vouchers', function (Blueprint $table) {
                $table->id();
                $table->text('evid')->nullable();
                $table->text('entry_date')->nullable();
                $table->text('type')->nullable();
                $table->text('party_id')->nullable();
                $table->text('tel')->nullable();
                $table->text('remarks')->nullable();
                $table->text('narration_id')->nullable();
                $table->text('row_account_head')->nullable();
                $table->text('row_account_id')->nullable();
                $table->text('amount')->nullable();
                $table->text('total_amount')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('account_transfers')) {
            Schema::create('account_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('atvid')->nullable();
                $table->date('transfer_date');
                $table->unsignedBigInteger('from_account_id');
                $table->unsignedBigInteger('to_account_id');
                $table->decimal('amount', 15, 2);
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('transfer_date');
                $table->index('atvid');
                $table->index('from_account_id');
                $table->index('to_account_id');
                $table->index('amount');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_vouchers');
        Schema::dropIfExists('payment_vouchers');
        Schema::dropIfExists('expense_vouchers');
        Schema::dropIfExists('account_transfers');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_heads');
    }
};
