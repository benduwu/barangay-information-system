<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurokResource extends JsonResource
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
            'purok_name' => $this->purok_name,
            'zone' => $this->zone,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
