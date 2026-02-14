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
        Schema::create('purchase_orders', function (Blueprint $table) {

            $table->id();


            // Foreign key to business_lists
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');


            //Supplier
            $table->unsignedBigInteger('vendors_id');
            $table->foreign('vendors_id')->references('id')->on('vendors')->onDelete('cascade');

            // Basic purchase order info
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['pending', 'received', 'partially_received', 'cancelled'])->default('pending');

            // Financials
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax', 15, 2)->default(0.00);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);

            // Payment info
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->string('payment_method')->nullable(); // e.g. cash, bank, credit
            $table->text('payment_note')->nullable();

            // Notes and attachments
            $table->text('notes')->nullable();
            $table->text('signature')->nullable();
            $table->string('attachment')->nullable(); // File path for PO invoice, etc.

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
