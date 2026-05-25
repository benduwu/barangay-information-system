<?php

namespace App\Events;

use App\Models\DocumentRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The document request instance.
     */
    public DocumentRequest $document;

    /**
     * The new status of the document.
     */
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(DocumentRequest $document, string $newStatus)
    {
        $this->document = $document;
        $this->newStatus = $newStatus;
    }
}
