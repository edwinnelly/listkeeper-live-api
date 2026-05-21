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
        Schema::create('productkeyhistories', function (Blueprint $table) {
           $table->ulid('id')->primary();
            $table->ulid('product_key_id')->nullable();

            $table->string('serial_number')->nullable();
            $table->enum('status', [
                'available',
                'sold',
                'reserved',
                'returned',
                'defective'
            ])->default('available');
            $table->ulid('assigned_to')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('sale_date')->nullable();

            $table->ulid('product_id')->nullable();

             $table->ulid('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->ulid('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');


            // Audit fields
            $table->enum('action_type', ['created', 'updated', 'deleted', 'status_changed'])->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productkeyhistories');
    }
};
