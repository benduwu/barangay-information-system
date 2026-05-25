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
        Schema::create('blotter_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blotter_id')->constrained('blotter_records')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('restrict');
            $table->string('previous_status');
            $table->string('new_status');
            $table->text('notes')->nullable();
            $table->timestamps(); // standard timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotter_updates');
    }
};
