<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ResidentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purok_id' => $this->purok_id,
            'purok_name' => $this->purok?->purok_name ?? 'N/A',
            'head_of_household_id' => $this->head_of_household_id,
            'head_of_household_name' => $this->headOfHousehold 
                ? "{$this->headOfHousehold->last_name}, {$this->headOfHousehold->first_name}" 
                : 'N/A',
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'civil_status' => $this->civil_status,
            'occupation' => $this->occupation,
            'is_voter' => $this->is_voter,
            'is_indigent' => $this->is_indigent,
            'is_pwd' => $this->is_pwd,
            'is_senior_citizen' => $this->is_senior_citizen,
            'photo_path' => $this->photo_path,
            'photo_url' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'created_by' => $this->created_by,
            'creator_name' => $this->creator?->full_name ?? 'System',
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
