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
        Schema::create('business_employees', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Employee details
            $table->string('name');
            $table->string('account_status')->default('on');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('position')->nullable(); // e.g. Manager, Staff
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();

            // Foreign key to business_lists
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_employees');
    }
};
