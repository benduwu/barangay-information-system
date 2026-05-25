<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Collection;

class TargetService
{
    /**
     * Resolve which resident IDs receive the blast.
     */
    public function getResidentIds(Announcement $announcement): array
    {
        return $this->getResidentsQuery($announcement)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Resolve Resident models that should receive the blast.
     */
    public function getResidents(Announcement $announcement): Collection
    {
        return $this->getResidentsQuery($announcement)->get();
    }

    /**
     * Build the query for target residents.
     */
    protected function getResidentsQuery(Announcement $announcement)
    {
        $query = Resident::where('is_active', true);

        if (!$announcement->is_barangay_wide) {
            $purokIds = $announcement->targets()->pluck('purok_id')->toArray();
            $query->whereIn('purok_id', $purokIds);
        }

        return $query;
    }
}
