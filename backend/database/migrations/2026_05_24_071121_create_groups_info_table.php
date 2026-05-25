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
        Schema::create('groups_info', function (Blueprint $table) {
            $table->foreignId('resident_id')->primary()->constrained('residents')->onDelete('cascade');
            $table->foreignId('head_of_household_id')->constrained('residents')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups_info');
    }
};
