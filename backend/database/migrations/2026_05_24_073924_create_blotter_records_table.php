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
        Schema::create('blotter_records', function (Blueprint $table) {
            $table->id();
            $table->string('blotter_number')->unique();
            $table->string('incident_type');
            $table->dateTime('incident_date');
            $table->string('incident_location');
            $table->text('incident_narrative');
            $table->string('status')->default('filed'); // filed, under_investigation, settled, escalated
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('settlement_details')->nullable();
            $table->foreignId('filed_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('incident_type');
            $table->index('blotter_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotter_records');
    }
};
