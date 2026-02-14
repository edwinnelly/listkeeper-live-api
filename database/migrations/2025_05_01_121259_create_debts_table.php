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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Foreign key to users
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            // Foreign key to business_lists
            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            // Foreign key to business_locations
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');

            // Foreign key to customers
            $table->unsignedBigInteger('customer_code');
            $table->foreign('customer_code')->references('id')->on('customers')->onDelete('cascade');

            // Debt details
            $table->string('debt_reference')->unique();
            $table->date('debt_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0.00);

            // PostgreSQL stored/generated column for balance
            $table->decimal('balance', 15, 2)->storedAs('amount - amount_paid');

            // Enum for status
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            $table->date('due_date')->nullable();

            // Optional tracking
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_overdue')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
