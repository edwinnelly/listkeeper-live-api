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
        Schema::create('product_sub_child_categories', function (Blueprint $table) {
            $table->id();

             // Link to business (optional, if categories are business-specific)
              // Foreign key to business_lists
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            // $table->unsignedBigInteger('location_id');
            // $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');


            $table->unsignedBigInteger('category_id'); // Link to top-level category
            $table->foreign('category_id')->references('id')->on('product_sub_categories')->onDelete('cascade');

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();


            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sub_child_categories');
    }
};
