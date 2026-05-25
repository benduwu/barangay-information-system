<?php

namespace App\Services;

use App\Models\WorkflowLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nDispatcher
{
    /**
     * Dispatch a webhook payload to an n8n workflow URL.
     *
     * @param string $workflowUrl  Full n8n webhook URL (e.g. http://localhost:5678/webhook/xxx)
     * @param array  $payload      Data to send in the POST body
     * @param string $workflowName Human-readable name for logging
     */
    public function dispatchWebhook(string $workflowUrl, array $payload, string $workflowName = 'unknown'): void
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Webhook-Secret' => config('services.n8n.webhook_secret', ''),
                    'Content-Type' => 'application/json',
                ])
                ->post($workflowUrl, $payload);

            WorkflowLog::create([
                'workflow_name' => $workflowName,
                'payload' => $payload,
                'status' => $response->successful() ? 'success' : 'failed',
                'response' => $response->body(),
                'triggered_at' => now(),
            ]);

            if ($response->failed()) {
                Log::warning("n8n workflow [{$workflowName}] failed", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            WorkflowLog::create([
                'workflow_name' => $workflowName,
                'payload' => $payload,
                'status' => 'failed',
                'response' => $e->getMessage(),
                'triggered_at' => now(),
            ]);

            Log::error("n8n dispatcher error [{$workflowName}]: {$e->getMessage()}");
        }
    }

    /**
     * Dispatch document status change to n8n.
     */
    public function dispatchDocumentStatus(array $payload): void
    {
        $url = config('services.n8n.webhooks.document_status');
        if ($url) {
            $this->dispatchWebhook($url, $payload, 'document-status-notifier');
        }
    }

    /**
     * Dispatch blotter status change to n8n.
     */
    public function dispatchBlotterStatus(array $payload): void
    {
        $url = config('services.n8n.webhooks.blotter_status');
        if ($url) {
            $this->dispatchWebhook($url, $payload, 'blotter-status-alert');
        }
    }

    /**
     * Dispatch announcement published to n8n.
     */
    public function dispatchAnnouncement(array $payload): void
    {
        $url = config('services.n8n.webhooks.announcement');
        if ($url) {
            $this->dispatchWebhook($url, $payload, 'announcement-blast');
        }
    }

    /**
     * Dispatch new resident created to n8n.
     */
    public function dispatchResidentCreated(array $payload): void
    {
        $url = config('services.n8n.webhooks.resident_created');
        if ($url) {
            $this->dispatchWebhook($url, $payload, 'new-resident-welcome');
        }
    }
}
