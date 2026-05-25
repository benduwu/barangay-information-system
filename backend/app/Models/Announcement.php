<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'posted_by',
        'title',
        'content',
        'image_path',
        'is_barangay_wide',
        'priority',
        'is_published',
        'publish_date',
        'expiry_date'
    ];

    protected $casts = [
        'is_barangay_wide' => 'boolean',
        'is_published' => 'boolean',
        'publish_date' => 'datetime',
        'expiry_date' => 'datetime'
    ];

    /**
     * Get the user who posted the announcement.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Get the target puroks for this announcement.
     */
    public function targets(): HasMany
    {
        return $this->hasMany(AnnouncementTarget::class);
    }
}
