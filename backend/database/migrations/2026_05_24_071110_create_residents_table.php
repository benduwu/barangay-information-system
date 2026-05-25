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
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purok_id')->constrained('puroks')->onDelete('restrict');
            $table->foreignId('head_of_household_id')->nullable()->constrained('residents')->onDelete('set null');
            $table->string('last_name', 100);
            $table->string('first_name', 100);
            $table->date('date_of_birth');
            $table->string('gender', 20); // Male, Female, Other
            $table->string('civil_status', 50); // Single, Married, Widowed, Divorced
            $table->string('occupation')->nullable();
            $table->boolean('is_voter')->default(false);
            $table->boolean('is_indigent')->default(false);
            $table->boolean('is_pwd')->default(false);
            $table->boolean('is_senior_citizen')->default(false);
            $table->string('photo_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for optimizing lookups and searches
            $table->index(['last_name', 'first_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
