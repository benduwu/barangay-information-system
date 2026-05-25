<?php

namespace App\Events;

use App\Models\Announcement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublished
{
    use Dispatchable, SerializesModels;

    public $announcement;
    public $targetResidentIds;

    /**
     * Create a new event instance.
     */
    public function __construct(Announcement $announcement, array $targetResidentIds)
    {
        $this->announcement = $announcement;
        $this->targetResidentIds = $targetResidentIds;
    }
}
