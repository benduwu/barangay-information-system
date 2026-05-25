<?php

namespace App\Events;

use App\Models\BlotterRecord;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlotterStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public BlotterRecord $blotter;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(BlotterRecord $blotter, string $oldStatus, string $newStatus)
    {
        $this->blotter = $blotter;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
