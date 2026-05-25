<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'resident_id',
        'processed_by',
        'document_type',
        'purpose',
        'control_number',
        'status',
        'rejection_reason',
        'amount',
        'is_paid',
        'official_receipt_no',
        'issued_date',
        'valid_until',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'issued_date' => 'datetime',
        'valid_until' => 'datetime',
    ];

    /**
     * Boot the model — auto-generate control_number on creating.
     */
    protected static function booted(): void
    {
        static::creating(function (DocumentRequest $doc) {
            if (empty($doc->control_number)) {
                $doc->control_number = self::generateControlNumber();
            }
        });
    }

    /**
     * Generate a unique sequential control number.
     * Format: BRG-{YEAR}-{5-digit-sequence}
     */
    public static function generateControlNumber(): string
    {
        $year = now()->year;
        $prefix = "BRG-{$year}-";

        $lastDoc = self::withTrashed()
            ->where('control_number', 'like', "{$prefix}%")
            ->orderByRaw("CAST(SUBSTR(control_number, " . (strlen($prefix) + 1) . ") AS INTEGER) DESC")
            ->first();

        if ($lastDoc) {
            $lastSequence = (int) substr($lastDoc->control_number, strlen($prefix));
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad($nextSequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get the resident who requested this document.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * Get the user who processed this document.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get a human-readable document type label.
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'clearance' => 'Barangay Clearance',
            'residency' => 'Certificate of Residency',
            'indigency' => 'Certificate of Indigency',
            default => ucfirst($this->document_type),
        };
    }
}
