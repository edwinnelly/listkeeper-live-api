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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // Business and Location (if multi-location system)
            // Foreign key to business_lists
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');

            // Account Details
            $table->string('name'); // e.g., "Main Bank Account"
            $table->string('account_number')->nullable(); // Optional for bank accounts
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('account_type')->default('bank');
            // Types: bank, cash, wallet, expense, revenue, asset, liability, equity

            // Financial Tracking
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);

            $table->date('opening_date')->nullable();

            // Optional metadata
            $table->string('currency')->default('USD');
            $table->text('note')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
