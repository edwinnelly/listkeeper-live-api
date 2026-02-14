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
            $table->id();
            $table->unsignedBigInteger('product_key_id');

            $table->string('serial_number')->nullable();
            $table->enum('status', [
                'available',
                'sold',
                'reserved',
                'returned',
                'defective'
            ])->default('available');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('sale_date')->nullable();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('business_key')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();

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
