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
        Schema::create('location_product_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('product_lists')->onDelete('cascade');

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');

            // $table->unsignedBigInteger('unit_id')->nullable();
            // $table->foreign('unit_id')->references('id')->on('product_units')->onDelete('cascade');

            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('vendors')->onDelete('cascade');

            // Pricing and stock
            $table->decimal('price', 15, 2); // Price per unit
            $table->decimal('cost_price', 15, 2)->nullable(); // Cost price (if needed)
            $table->decimal('sale_price', 15, 2)->nullable(); // Discounted or sale price (if applicable)
            $table->integer('stock_quantity')->default(0); // Available stock
            $table->integer('low_stock_threshold')->default(10); // Warning threshold for low stock

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_product_lists');
    }
};
