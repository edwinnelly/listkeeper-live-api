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
        Schema::create('finance_journal_entries', function (Blueprint $table) {

            $table->id();

            // $table->foreignId('journal_id')->constrained()->onDelete('cascade');
            // $table->foreignId('account_id')->constrained()->onDelete('cascade');

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->string('description')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable(); // optional link to customer/vendor
            $table->string('contact_type')->nullable(); // polymorphic

            $table->timestamps();


            // Business and Location (if multi-location system)
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
        Schema::dropIfExists('finance_journal_entries');
    }
};
