<?php

namespace App\Events;

use App\Models\Resident;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidentCreated
{
    use Dispatchable, SerializesModels;

    public Resident $resident;

    /**
     * Create a new event instance.
     */
    public function __construct(Resident $resident)
    {
        $this->resident = $resident;
    }
}
