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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();

            // Business and Location (if multi-location system)
            // Foreign key to business_lists
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');

            $table->string('name'); // e.g., "VAT", "GST", "Sales Tax"
            $table->decimal('rate', 8, 3); // e.g., 7.5 for 7.5%
            $table->string('type')->default('percentage'); // percentage or fixed
            $table->boolean('is_compound')->default(false); // if applied on top of another tax
            $table->boolean('is_inclusive')->default(false); // included in price or added

            $table->string('region')->nullable(); // optional - "Nigeria", "EU", etc.
            $table->string('tax_code')->nullable(); // optional tax authority code

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
