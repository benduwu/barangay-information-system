<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportLog extends Model
{
    // Disabling standard Laravel timestamps because we only use generated_at
    public $timestamps = false;

    protected $fillable = [
        'generated_by',
        'report_type',
        'parameters',
        'file_path',
        'generated_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Get the user who generated this report.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
