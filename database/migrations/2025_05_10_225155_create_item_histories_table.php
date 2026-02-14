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
        Schema::create('item_histories', function (Blueprint $table) {

            $table->id();

            // Foreign Keys
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('product_lists')->onDelete('cascade');
            // Business and Location (if multi-location system)
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');

            // Transaction Info
            $table->string('type'); // purchase, sale, return, adjustment, transfer, etc.
            $table->decimal('quantity', 15, 4);
            $table->decimal('cost', 15, 4)->nullable(); // optional: per unit cost at time of transaction
            $table->decimal('price', 15, 4)->nullable(); // optional: per unit sale price

            // Polymorphic source (e.g., Invoice, PurchaseOrder, etc.)
            $table->morphs('source'); // source_id + source_type

            $table->string('note')->nullable();
            $table->timestamp('transaction_date')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_histories');
    }
};
