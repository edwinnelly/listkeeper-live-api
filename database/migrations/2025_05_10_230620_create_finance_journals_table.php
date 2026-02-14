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
        Schema::create('finance_journals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('business_key');
            $table->foreign('business_key')->references('business_key')->on('business_lists')->onDelete('cascade');

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');


            $table->string('reference')->nullable(); // e.g. INV-1001, PO-205
            $table->string('type')->nullable(); // e.g. "invoice", "payment", "adjustment", etc.

            $table->string('description')->nullable();
            $table->timestamp('journal_date')->useCurrent();

            // Polymorphic relation (optional): Link to invoice, purchase, etc.
            $table->nullableMorphs('source'); // source_id, source_type

            $table->boolean('is_posted')->default(false); // indicates if finalized

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_journals');
    }
};
