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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posted_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('image_path')->nullable();
            $table->boolean('is_barangay_wide')->default(true);
            $table->string('priority')->default('normal'); // low, normal, high
            $table->boolean('is_published')->default(false);
            $table->dateTime('publish_date')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
