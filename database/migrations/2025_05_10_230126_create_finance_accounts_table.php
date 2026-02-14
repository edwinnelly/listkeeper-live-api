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
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->nullable(); // optional account code like "1000" or "EXP001"
            $table->enum('type', [
                'asset',
                'liability',
                'equity',
                'income',
                'expense',
                'other_income',
                'other_expense'
            ]);

            $table->enum('sub_type', [
                'cash',
                'bank',
                'accounts_receivable',
                'accounts_payable',
                'sales',
                'purchase',
                'inventory',
                'fixed_asset',
                'depreciation',
                'loan',
                'capital',
                'drawings',
                'payroll_expense',
                'tax',
                'others'
            ])->nullable();

            $table->boolean('is_default')->default(false); // system-defined account
            $table->boolean('is_active')->default(true);


             $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_accounts');
    }
};
