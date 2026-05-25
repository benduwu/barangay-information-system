<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WorkflowLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Verify shared secret from n8n webhook requests.
     */
    protected function verifySecret(Request $request): bool
    {
        $expected = config('services.n8n.webhook_secret');
        if (!$expected) {
            return true; // No secret configured, allow in dev
        }
        return $request->header('X-Webhook-Secret') === $expected;
    }

    /**
     * Receive document status webhook (called BY n8n for reverse flow if needed).
     */
    public function documentStatus(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'document_id' => ['required', 'integer'],
            'status' => ['required', 'string'],
        ]);

        $document = \App\Models\DocumentRequest::findOrFail($validated['document_id']);
        $oldStatus = $document->status;

        if ($oldStatus !== $validated['status']) {
            $document->status = $validated['status'];
            $document->save();
            event(new \App\Events\DocumentStatusChanged($document, $validated['status']));
        }

        // Log the inbound webhook
        WorkflowLog::create([
            'workflow_name' => 'inbound-document-status',
            'payload' => $validated,
            'status' => 'success',
            'response' => 'Webhook received and DocumentStatusChanged event dispatched.',
            'triggered_at' => now(),
        ]);

        return response()->json(['message' => 'Document status webhook received and event dispatched.']);
    }

    /**
     * Receive blotter status webhook (called BY n8n for reverse flow if needed).
     */
    public function blotterStatus(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'blotter_id' => ['required', 'integer'],
            'status' => ['required', 'string'],
        ]);

        $blotter = \App\Models\BlotterRecord::findOrFail($validated['blotter_id']);
        $oldStatus = $blotter->status;

        if ($oldStatus !== $validated['status']) {
            $blotter->status = $validated['status'];
            $blotter->save(); // This automatically fires BlotterStatusChanged event from BlotterObserver
        }

        WorkflowLog::create([
            'workflow_name' => 'inbound-blotter-status',
            'payload' => $validated,
            'status' => 'success',
            'response' => 'Webhook received and BlotterStatusChanged event dispatched.',
            'triggered_at' => now(),
        ]);

        return response()->json(['message' => 'Blotter status webhook received and event dispatched.']);
    }

    /**
     * Receive announcement webhook (called BY n8n for reverse flow if needed).
     */
    public function announcement(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'announcement_id' => ['required', 'integer'],
        ]);

        $announcement = \App\Models\Announcement::findOrFail($validated['announcement_id']);
        
        $targetService = app(\App\Services\TargetService::class);
        $targetResidentIds = $targetService->getResidentIds($announcement);

        event(new \App\Events\AnnouncementPublished($announcement, $targetResidentIds));

        WorkflowLog::create([
            'workflow_name' => 'inbound-announcement',
            'payload' => $validated,
            'status' => 'success',
            'response' => 'Webhook received and AnnouncementPublished event dispatched.',
            'triggered_at' => now(),
        ]);

        return response()->json(['message' => 'Announcement webhook received and event dispatched.']);
    }

    /**
     * Receive workflow log result from n8n (called by final node of every workflow).
     */
    public function logResult(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'workflow_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:success,failed'],
            'payload' => ['nullable', 'array'],
            'response' => ['nullable', 'string'],
        ]);

        WorkflowLog::create([
            'workflow_name' => $validated['workflow_name'],
            'payload' => $validated['payload'] ?? null,
            'status' => $validated['status'],
            'response' => $validated['response'] ?? null,
            'triggered_at' => now(),
        ]);

        return response()->json(['message' => 'Workflow log recorded.']);
    }
}
