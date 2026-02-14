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
        Schema::create('business_lists', function (Blueprint $table) {
            $table->id();

            // the owner (linked to users table)
            $table->unsignedBigInteger('owner_id');
            // $table->unsignedBigInteger('business_key')->unique();
            $table->string('business_key')->unique();

            //FK
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_name');
            $table->string('slug')->unique(); // for SEO-friendly URLs
            $table->string('registration_no')->nullable();
            $table->string('industry_type')->nullable(); // e.g., Retail, IT, etc.
            $table->string('email')->nullable();
            $table->string('currency')->nullable();
            $table->string('website')->nullable();
            $table->string('about_business')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('country')->nullable();
            $table->string('subscription_type')->nullable();
            $table->string('subscription_plan')->nullable();
            $table->string('language')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('logo')->nullable(); // business logo file path
            $table->enum('status', ['active', 'inactive', 'pending','suspended'])->default('pending');
            $table->timestamps();
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_lists');
    }
};
