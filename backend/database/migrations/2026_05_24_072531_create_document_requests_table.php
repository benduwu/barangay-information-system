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
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('restrict');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('document_type'); // clearance, residency, indigency
            $table->text('purpose');
            $table->string('control_number')->unique();
            $table->string('status')->default('pending'); // pending, approved, rejected, released
            $table->text('rejection_reason')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->boolean('is_paid')->default(false);
            $table->string('official_receipt_no')->nullable();
            $table->timestamp('issued_date')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('status');
            $table->index('document_type');
            $table->index('control_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
