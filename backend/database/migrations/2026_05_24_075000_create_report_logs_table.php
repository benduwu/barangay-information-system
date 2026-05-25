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
        Schema::create('report_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->string('report_type'); // residents, documents, blotters, monthly
            $table->json('parameters')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_logs');
    }
};
