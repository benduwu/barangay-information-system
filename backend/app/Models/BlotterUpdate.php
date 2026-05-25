<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlotterUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'blotter_id',
        'updated_by',
        'previous_status',
        'new_status',
        'notes',
    ];

    /**
     * Get the blotter record associated with this update.
     */
    public function blotter(): BelongsTo
    {
        return $this->belongsTo(BlotterRecord::class, 'blotter_id');
    }

    /**
     * Get the user who made this update.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
