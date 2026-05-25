<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlotterResource extends JsonResource
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
            'blotter_number' => $this->blotter_number,
            'incident_type' => $this->incident_type,
            'incident_date' => $this->incident_date?->toIso8601String(),
            'incident_location' => $this->incident_location,
            'incident_narrative' => $this->incident_narrative,
            'status' => $this->status,
            'assigned_officer_id' => $this->assigned_officer_id,
            'assigned_officer_name' => $this->officer?->full_name ?? 'Unassigned',
            'settlement_details' => $this->settlement_details,
            'filed_by' => $this->filed_by,
            'creator_name' => $this->creator?->full_name ?? 'Unknown',
            
            // Included relation lists formatted cleanly
            'parties' => $this->relationLoaded('parties') ? $this->parties->map(fn($p) => [
                'id' => $p->id,
                'resident_id' => $p->resident_id,
                'role' => $p->role,
                'full_name' => $p->full_name,
                'address' => $p->address,
                'contact_number' => $p->contact_number,
                'statement' => $p->statement,
            ]) : [],

            'updates' => $this->relationLoaded('updates') ? $this->updates->map(fn($u) => [
                'id' => $u->id,
                'previous_status' => $u->previous_status,
                'new_status' => $u->new_status,
                'notes' => $u->notes,
                'updated_by_name' => $u->updater?->full_name ?? 'System',
                'created_at' => $u->created_at?->toIso8601String(),
            ]) : [],

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
