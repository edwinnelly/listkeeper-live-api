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
        Schema::create('stock_transfers', function (Blueprint $table) {
          $table->ulid('id')->primary();
            $table->ulid('from_location_id');
            $table->ulid('to_location_id');
            $table->date('transfer_date');
            $table->date('expected_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable()->unique();
            $table->enum('status', ['approved', 'pending', 'suspended'])->default('pending');
            // Item details (since always one product)
            $table->ulid('product_id');
            $table->unsignedInteger('stock_quantity');
            $table->unsignedInteger('stock_quantity_before');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->cascadeOnDelete();


            $table->string('postby')->nullable();
            $table->string('received_by')->nullable();

            $table->timestamps();

            // Foreign keys for locations
            $table->foreign('from_location_id')->references('id')->on('business_locations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('to_location_id')->references('id')->on('business_locations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('product_id')->references('id')->on('product_lists')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
