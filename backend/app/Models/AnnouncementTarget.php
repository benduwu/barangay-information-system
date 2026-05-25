<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementTarget extends Model
{
    protected $fillable = [
        'announcement_id',
        'purok_id'
    ];

    /**
     * Get the announcement that this target belongs to.
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Get the purok target.
     */
    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }
}
