<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\clear;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            // FK to users table - unique role per user
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // FK to business_lists using 'id' for standard consistency
            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            // FK to users table for owner reference
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            // Admin Permission
            $table->enum('permission', ['yes', 'no'])->default('no');

            // Users roles
            $table->enum('users_create', ['yes', 'no'])->default('no');
            $table->enum('users_read', ['yes', 'no'])->default('no');
            $table->enum('users_update', ['yes', 'no'])->default('no');
            $table->enum('users_delete', ['yes', 'no'])->default('no');

            // Subscriptions
            $table->enum('subscriptions_read', ['yes', 'no'])->default('no');
            $table->enum('subscriptions_update', ['yes', 'no'])->default('no');

            // Locations roles
            $table->enum('locations_create', ['yes', 'no'])->default('no');
            $table->enum('locations_read', ['yes', 'no'])->default('no');
            $table->enum('locations_update', ['yes', 'no'])->default('no');
            $table->enum('locations_delete', ['yes', 'no'])->default('no');
            $table->enum('locations_analytics', ['yes', 'no'])->default('no');


            // Product category roles
            $table->enum('category_create', ['yes', 'no'])->default('no');
            $table->enum('category_read', ['yes', 'no'])->default('no');
            $table->enum('category_update', ['yes', 'no'])->default('no');
            $table->enum('category_delete', ['yes', 'no'])->default('no');

            //manage  Product roles
            $table->enum('product_create', ['yes', 'no'])->default('no');
            $table->enum('product_read', ['yes', 'no'])->default('no');
            $table->enum('product_update', ['yes', 'no'])->default('no');
            $table->enum('product_delete', ['yes', 'no'])->default('no');


            //manage  unit roles
            $table->enum('unit_create', ['yes', 'no'])->default('no');
            $table->enum('unit_read', ['yes', 'no'])->default('no');
            $table->enum('unit_update', ['yes', 'no'])->default('no');
            $table->enum('unit_delete', ['yes', 'no'])->default('no');


            //manage  unit roles
            $table->enum('vendor_create', ['yes', 'no'])->default('no');
            $table->enum('vendor_read', ['yes', 'no'])->default('no');
            $table->enum('vendor_update', ['yes', 'no'])->default('no');
            $table->enum('vendor_delete', ['yes', 'no'])->default('no');


            //manage  purchase roles
            $table->enum('purchase_create', ['yes', 'no'])->default('no');
            $table->enum('purchase_read', ['yes', 'no'])->default('no');
            $table->enum('purchase_update', ['yes', 'no'])->default('no');
            $table->enum('purchase_delete', ['yes', 'no'])->default('no');


            //manage  customer roles
            $table->enum('customer_create', ['yes', 'no'])->default('no');
            $table->enum('customer_read', ['yes', 'no'])->default('no');
            $table->enum('customer_update', ['yes', 'no'])->default('no');
            $table->enum('customer_delete', ['yes', 'no'])->default('no');


            //manage  credit note roles
            $table->enum('credit_note_create', ['yes', 'no'])->default('no');
            $table->enum('credit_note_read', ['yes', 'no'])->default('no');
            $table->enum('credit_note_update', ['yes', 'no'])->default('no');
            $table->enum('credit_note_delete', ['yes', 'no'])->default('no');


            //manage  expenses roles
            $table->enum('expense_create', ['yes', 'no'])->default('no');
            $table->enum('expense_read', ['yes', 'no'])->default('no');
            $table->enum('expense_update', ['yes', 'no'])->default('no');
            $table->enum('expense_delete', ['yes', 'no'])->default('no');


            //manage  invoice roles
            $table->enum('invoice_create', ['yes', 'no'])->default('no');
            $table->enum('invoice_read', ['yes', 'no'])->default('no');
            $table->enum('invoice_update', ['yes', 'no'])->default('no');
            $table->enum('invoice_delete', ['yes', 'no'])->default('no');


            //manage  POS roles
            $table->enum('pos_create', ['yes', 'no'])->default('no');
            $table->enum('pos_read', ['yes', 'no'])->default('no');
            $table->enum('pos_update', ['yes', 'no'])->default('no');
            $table->enum('pos_delete', ['yes', 'no'])->default('no');




            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
