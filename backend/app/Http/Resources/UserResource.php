<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'username'   => $this->username,
            'email'      => $this->email,
            'full_name'  => $this->full_name,
            'is_active'  => $this->is_active,
            'last_login' => $this->last_login?->toIso8601String(),
            'role'       => $this->getRoleNames()->first() ?? 'none',
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
