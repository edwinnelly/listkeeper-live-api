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
        Schema::create('product_lists', function (Blueprint $table) {
            $table->id();

            // Foreign key to business_lists
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            // Product general info
            $table->string('name');
            $table->string('sku')->nullable(); // Stock Keeping Unit, for inventory management
            $table->string('barcode')->nullable(); // barcode, for inventory management
            $table->text('description')->nullable();
            $table->string('slug')->nullable(); // SEO-friendly URL
            $table->string('dimensions')->nullable(); // SEO-friendly URL

            // Product categorization
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');

            // Sub Product categorization
            $table->unsignedBigInteger('sub_category_id')->default(0);
            // $table->foreign('sub_category_id')->references('id')->on('product_sub_categories')->onDelete('cascade');

            // Child Product categorization
            $table->unsignedBigInteger('child_sub_category_id')->default(0);
            // $table->foreign('child_sub_category_id')->references('id')->on('product_sub_child_categories')->onDelete('cascade');


            // Product unit (link to product_units table)
            $table->string('product_measurements')->nullable(); // Correct column
            // Pricing and stock
            $table->decimal('price', 15, 2)->nullable(); // Price per unit
            $table->decimal('cost_price', 15, 2)->nullable(); // Cost price (if needed)
            $table->decimal('sale_price', 15, 2)->nullable(); // Discounted or sale price (if applicable)
            $table->decimal('stock_quantity', 15, 2)->nullable(); // Discounted or sale price (if applicable)
            $table->decimal('low_stock_threshold', 15, 2)->nullable(); // Discounted or sale price (if applicable)

            // Discounts and Offers
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Discount on product (if applicable)
            $table->date('discount_start_date')->nullable(); // Discount start date
            $table->date('discount_end_date')->nullable(); // Discount end date
            $table->date('manufactured_at')->nullable(); // manufactured_at /  date
            $table->date('expires_at')->nullable(); // expires_at /  date

            // Product weight (optional)
            $table->decimal('weight', 10, 2)->nullable(); // In kilograms (or relevant unit)

            // Product dimensions (optional)
            $table->string('length')->nullable(); // In centimeters (optional)
            $table->string('width')->nullable();  // In centimeters (optional)
            $table->string('height')->nullable(); // In centimeters (optional)

            // Product supplier details
            $table->unsignedBigInteger('supplier_id')->nullable(); // Supplier ID
            $table->foreign('supplier_id')->references('id')->on('vendors')->onDelete('set null'); // Foreign key to supplier

            // Product status
            $table->boolean('is_active')->default(true); // Is product available for sale
            $table->boolean('is_featured')->default(false); // Featured product flag
            $table->boolean('is_on_sale')->default(false); // Flag for products on sale
            $table->boolean('is_out_of_stock')->default(false); // Flag for out-of-stock products

            // Product images (JSON format for storing multiple images)
            $table->string('image')->nullable(); // Store array of image URLs (e.g., main image and gallery)

            // Additional metadata (if needed)
            $table->json('additional_info')->nullable(); // Store extra details like colors, sizes, etc.

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_lists');
    }
};
