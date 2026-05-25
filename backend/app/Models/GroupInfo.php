<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupInfo extends Model
{
    use HasFactory;

    protected $table = 'groups_info';

    protected $primaryKey = 'resident_id';

    public $incrementing = false;

    protected $fillable = [
        'resident_id',
        'head_of_household_id',
    ];

    /**
     * Get the resident this group record belongs to.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    /**
     * Get the head of household this group record belongs to.
     */
    public function headOfHousehold(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'head_of_household_id');
    }
}
