<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purok_id',
        'head_of_household_id',
        'last_name',
        'first_name',
        'date_of_birth',
        'gender',
        'civil_status',
        'occupation',
        'is_voter',
        'is_indigent',
        'is_pwd',
        'is_senior_citizen',
        'photo_path',
        'created_by',
        'is_active',
        'contact_number',
        'email',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_voter' => 'boolean',
        'is_indigent' => 'boolean',
        'is_pwd' => 'boolean',
        'is_senior_citizen' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the purok this resident belongs to.
     */
    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }

    /**
     * Get the head of household for this resident.
     */
    public function headOfHousehold(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'head_of_household_id');
    }

    /**
     * Get the residents who have this resident as their household head.
     */
    public function householdMembers(): HasMany
    {
        return $this->hasMany(Resident::class, 'head_of_household_id');
    }

    /**
     * Get the user who registered this resident.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper to get full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->last_name}, {$this->first_name}";
    }
}
