<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlotterParty extends Model
{
    use HasFactory;

    protected $fillable = [
        'blotter_id',
        'resident_id',
        'role',
        'full_name',
        'address',
        'contact_number',
        'statement',
    ];

    /**
     * Get the blotter record associated with this party.
     */
    public function blotter(): BelongsTo
    {
        return $this->belongsTo(BlotterRecord::class, 'blotter_id');
    }

    /**
     * Get the resident associated with this party, if any.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }
}
