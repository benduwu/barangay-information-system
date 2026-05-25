<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            'resident_id' => $this->resident_id,
            'resident_name' => $this->resident
                ? "{$this->resident->last_name}, {$this->resident->first_name}"
                : 'N/A',
            'processed_by' => $this->processed_by,
            'processor_name' => $this->processor?->full_name ?? 'Unprocessed',
            'document_type' => $this->document_type,
            'document_type_label' => $this->document_type_label,
            'purpose' => $this->purpose,
            'control_number' => $this->control_number,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'amount' => $this->amount,
            'is_paid' => $this->is_paid,
            'official_receipt_no' => $this->official_receipt_no,
            'issued_date' => $this->issued_date?->toIso8601String(),
            'valid_until' => $this->valid_until?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
