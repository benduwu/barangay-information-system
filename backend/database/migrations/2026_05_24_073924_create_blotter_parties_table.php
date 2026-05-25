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
        Schema::create('blotter_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blotter_id')->constrained('blotter_records')->onDelete('cascade');
            $table->foreignId('resident_id')->nullable()->constrained('residents')->onDelete('restrict');
            $table->string('role'); // complainant, respondent, witness
            $table->string('full_name');
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('statement')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotter_parties');
    }
};
