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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Full name of the user
            $table->string('creator')->default('Host'); // Unique business name
            $table->string('email')->unique(); // Unique email address
            $table->timestamp('email_verified_at')->nullable(); // Email verification timestamp

            $table->string('phone_number')->nullable(); // Optional phone number
            $table->string('address')->nullable(); // Address of the user/business
            $table->string('city')->nullable(); // City location
            $table->string('state')->nullable(); // State location
            $table->string('age')->nullable(); // State location
            $table->string('postal_code')->nullable(); // Postal/ZIP code
            $table->string('country')->nullable(); // Country
            $table->string('about')->nullable(); // Country
            $table->string('profile_pic')->nullable(); // Country
            $table->enum('account_tier', ['yes', 'no'])->default('no');
            $table->unsignedBigInteger('locations')->nullable();
            $table->string('password'); // Hashed password

            $table->string('business_key')->nullable(); // Optional unique business identifier

            $table->string('active_business_key')->default('0'); // Currently active business (if multi-business supported)

            $table->string('role')->default('user');


            $table->boolean('is_active')->default(true); // Whether the account is active
            $table->timestamp('last_login_at')->nullable(); // Last login timestamp
            $table->string('profile_photo')->nullable(); // Profile photo path

            $table->rememberToken(); // For "remember me" functionality
            $table->timestamps(); // created_at and updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
