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
        Schema::create('workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_name');
            $table->json('payload')->nullable();
            $table->string('status')->default('success'); // success, failed
            $table->text('response')->nullable();
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamps();

            $table->index('workflow_name');
            $table->index('status');
            $table->index('triggered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_logs');
    }
};
