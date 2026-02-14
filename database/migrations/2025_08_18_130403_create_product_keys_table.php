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
        Schema::create('product_keys', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->nullable();
            $table->string('username')->nullable();
            $table->enum('status', [
                'available',
                'sold',
                'reserved',
                'returned',
                'defective'
            ])->default('available');
            $table->unsignedBigInteger('assigned_to')->nullable(); // could be customer_id or order_id
            $table->date('purchase_date')->nullable();
            $table->date('sale_date')->nullable();

            // Foreign key to business_lists
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('product_lists')->onDelete('cascade');

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
        Schema::dropIfExists('product_keys');
    }
};
