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
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();

            // Business and Location (if multi-location system)
            // Foreign key to business_lists
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');

            // Category info
            $table->string('name'); // e.g., "Utilities", "Marketing", "Office Supplies"
            $table->string('code')->nullable(); // Optional: shorthand or accounting code
            $table->text('description')->nullable();

            // Optional hierarchy support
            // $table->unsignedBigInteger('parent_id')->nullable();
            // $table->foreign('parent_id')->references('id')->on('expense_categories')->onDelete('set null');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
