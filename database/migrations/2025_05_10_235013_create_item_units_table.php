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
        Schema::create('item_units', function (Blueprint $table) {
            $table->id();

            // Business and Location (if multi-location system)
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('product_lists')->onDelete('cascade');
            $table->string('serial_number')->nullable()->unique(); // For serialized items
            $table->string('batch_number')->nullable();            // For batch tracking
            $table->date('expiry_date')->nullable();               // For perishable goods

            $table->decimal('purchase_cost', 15, 2)->nullable();   // Cost per unit
            $table->decimal('selling_price', 15, 2)->nullable();   // Optional override

            $table->enum('status', ['in_stock', 'sold', 'damaged', 'transferred'])->default('in_stock');

            $table->timestamp('added_at')->nullable();             // When added to stock
            $table->timestamp('removed_at')->nullable();           // When removed (sale, discard)

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
