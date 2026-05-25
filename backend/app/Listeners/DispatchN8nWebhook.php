<?php

namespace App\Listeners;

use App\Events\DocumentStatusChanged;
use App\Events\BlotterStatusChanged;
use App\Events\AnnouncementPublished;
use App\Events\ResidentCreated;
use App\Services\N8nDispatcher;

class DispatchN8nWebhook
{
    protected N8nDispatcher $dispatcher;

    public function __construct(N8nDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle DocumentStatusChanged event.
     */
    public function handleDocumentStatus(DocumentStatusChanged $event): void
    {
        $doc = $event->document->load('resident');

        $this->dispatcher->dispatchDocumentStatus([
            'document_id' => $doc->id,
            'control_number' => $doc->control_number,
            'document_type' => $doc->document_type,
            'status' => $event->newStatus,
            'rejection_reason' => $doc->rejection_reason,
            'resident_name' => $doc->resident ? $doc->resident->first_name . ' ' . $doc->resident->last_name : null,
            'resident_email' => $doc->resident->email ?? null,
            'resident_phone' => $doc->resident->contact_number ?? null,
            'pdf_url' => url("/api/v1/documents/{$doc->id}/pdf"),
        ]);
    }

    /**
     * Handle BlotterStatusChanged event.
     */
    public function handleBlotterStatus(BlotterStatusChanged $event): void
    {
        $blotter = $event->blotter->load(['parties', 'officer']);

        $this->dispatcher->dispatchBlotterStatus([
            'blotter_id' => $blotter->id,
            'blotter_number' => $blotter->blotter_number,
            'incident_type' => $blotter->incident_type,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'incident_date' => $blotter->incident_date,
            'incident_location' => $blotter->incident_location,
            'settlement_details' => $blotter->settlement_details,
            'assigned_officer' => $blotter->officer ? $blotter->officer->full_name : null,
            'officer_email' => $blotter->officer->email ?? null,
            'parties' => $blotter->parties->map(fn($p) => [
                'role' => $p->role,
                'full_name' => $p->full_name,
                'contact_number' => $p->contact_number,
            ])->toArray(),
        ]);
    }

    /**
     * Handle AnnouncementPublished event.
     */
    public function handleAnnouncement(AnnouncementPublished $event): void
    {
        $ann = $event->announcement->load('targets.purok');

        $this->dispatcher->dispatchAnnouncement([
            'announcement_id' => $ann->id,
            'title' => $ann->title,
            'content' => $ann->content,
            'priority' => $ann->priority,
            'is_barangay_wide' => $ann->is_barangay_wide,
            'target_purok_ids' => $ann->targets->pluck('purok_id')->toArray(),
            'target_resident_ids' => $event->targetResidentIds,
        ]);
    }

    /**
     * Handle ResidentCreated event.
     */
    public function handleResidentCreated(ResidentCreated $event): void
    {
        $resident = $event->resident->load('purok');

        $this->dispatcher->dispatchResidentCreated([
            'resident_id' => $resident->id,
            'first_name' => $resident->first_name,
            'last_name' => $resident->last_name,
            'email' => $resident->email ?? null,
            'contact_number' => $resident->contact_number ?? null,
            'purok_name' => $resident->purok?->purok_name,
        ]);
    }

    /**
     * Subscribe to multiple events.
     */
    public function subscribe($events): array
    {
        return [
            DocumentStatusChanged::class => 'handleDocumentStatus',
            BlotterStatusChanged::class => 'handleBlotterStatus',
            AnnouncementPublished::class => 'handleAnnouncement',
            ResidentCreated::class => 'handleResidentCreated',
        ];
    }
}
