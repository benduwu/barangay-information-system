<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlotterRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'blotter_number',
        'incident_type',
        'incident_date',
        'incident_location',
        'incident_narrative',
        'status',
        'assigned_officer_id',
        'settlement_details',
        'filed_by',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
    ];

    /**
     * Boot the model — auto-generate blotter_number on creating.
     */
    protected static function booted(): void
    {
        static::creating(function (BlotterRecord $blotter) {
            if (empty($blotter->blotter_number)) {
                $blotter->blotter_number = self::generateBlotterNumber();
            }
        });
    }

    /**
     * Generate a unique sequential blotter number.
     * Format: BLT-{YEAR}-{5-digit-sequence}
     */
    public static function generateBlotterNumber(): string
    {
        $year = now()->year;
        $prefix = "BLT-{$year}-";

        $lastRec = self::withTrashed()
            ->where('blotter_number', 'like', "{$prefix}%")
            ->orderByRaw("CAST(SUBSTR(blotter_number, " . (strlen($prefix) + 1) . ") AS INTEGER) DESC")
            ->first();

        if ($lastRec) {
            $lastSequence = (int) substr($lastRec->blotter_number, strlen($prefix));
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad($nextSequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Parties involved in this blotter incident.
     */
    public function parties(): HasMany
    {
        return $this->hasMany(BlotterParty::class, 'blotter_id');
    }

    /**
     * Status updates / history trail for this blotter incident.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(BlotterUpdate::class, 'blotter_id');
    }

    /**
     * Investigator / officer assigned to this case.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    /**
     * User who filed this blotter report.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }
}
