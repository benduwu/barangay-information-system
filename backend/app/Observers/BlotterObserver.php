<?php

namespace App\Observers;

use App\Models\BlotterRecord;
use App\Events\BlotterStatusChanged;

class BlotterObserver
{
    /**
     * Handle the BlotterRecord "updating" event.
     */
    public function updating(BlotterRecord $blotter): void
    {
        if ($blotter->isDirty('status')) {
            $oldStatus = $blotter->getOriginal('status') ?? 'filed';
            $newStatus = $blotter->status;

            event(new BlotterStatusChanged($blotter, $oldStatus, $newStatus));
        }
    }
}
