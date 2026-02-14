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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('owner_id'); // user subscribed
            $table->string('plan_name')->nullable();       // e.g., Basic, Pro, Enterprise
            $table->string('plan_code')->nullable(); // optional code from external payment provider
            $table->decimal('amount', 10, 2);      // subscription price
            $table->string('currency', 10)->default('USD')->nullable();

            $table->timestamp('start_date')->nullable();     // when subscription started
            $table->timestamp('end_date')->nullable();      // when subscription ends
            $table->enum('status', ['active', 'expired', 'cancelled', 'inactive'])->default('inactive');

            $table->string('payment_method')->nullable();  // e.g., Card, Bank Transfer
            $table->string('transaction_id')->nullable();  // for tracking payments
            $table->string('users')->default(0);  // for tracking payments
            $table->string('products')->default(0);  // for tracking payments
            $table->string('locations')->default(0);  // for tracking payments
            $table->string('invoice')->default(0);  // for tracking payments

            $table->timestamps();

            // Foreign key to business_lists
            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            // Foreign key
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
