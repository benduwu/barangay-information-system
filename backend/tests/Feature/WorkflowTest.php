<?php

namespace Tests\Feature;

use App\Events\AnnouncementPublished;
use App\Events\BlotterStatusChanged;
use App\Events\DocumentStatusChanged;
use App\Events\ResidentCreated;
use App\Models\Announcement;
use App\Models\BlotterRecord;
use App\Models\DocumentRequest;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\User;
use App\Models\WorkflowLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Purok $purok;
    protected Resident $resident;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::create([
            'username' => 'testadmin',
            'email' => 'admin@test.com',
            'full_name' => 'Test Admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');

        $this->purok = Purok::create([
            'purok_name' => 'Purok 1',
            'details' => 'Zone 1',
        ]);

        $this->resident = Resident::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'civil_status' => 'Single',
            'contact_number' => '09123456789',
            'email' => 'juan@example.com',
            'purok_id' => $this->purok->id,
            'is_voter' => true,
            'is_indigent' => false,
            'is_pwd' => false,
            'is_senior_citizen' => false,
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
    }

    /**
     * Test inbound document status webhook.
     */
    public function test_inbound_document_status_webhook_dispatches_event(): void
    {
        Event::fake([DocumentStatusChanged::class]);

        $document = DocumentRequest::create([
            'resident_id' => $this->resident->id,
            'document_type' => 'clearance',
            'purpose' => 'Employment',
            'status' => 'pending',
            'amount' => 0.00,
            'is_paid' => false,
        ]);

        $response = $this->postJson('/api/v1/webhooks/document-status', [
            'document_id' => $document->id,
            'status' => 'approved',
        ], [
            'X-Webhook-Secret' => 'barangay_n8n_secret_2026',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Document status webhook received and event dispatched.']);

        $this->assertDatabaseHas('document_requests', [
            'id' => $document->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_name' => 'inbound-document-status',
            'status' => 'success',
        ]);

        Event::assertDispatched(DocumentStatusChanged::class, function ($event) use ($document) {
            return $event->document->id === $document->id && $event->newStatus === 'approved';
        });
    }

    /**
     * Test inbound document status webhook fails with invalid secret.
     */
    public function test_inbound_document_status_webhook_requires_valid_secret(): void
    {
        $response = $this->postJson('/api/v1/webhooks/document-status', [
            'document_id' => 1,
            'status' => 'approved',
        ], [
            'X-Webhook-Secret' => 'wrong_secret',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test inbound blotter status webhook.
     */
    public function test_inbound_blotter_status_webhook_dispatches_event(): void
    {
        Event::fake([BlotterStatusChanged::class]);

        $blotter = BlotterRecord::create([
            'blotter_number' => 'BLT-2026-0001',
            'incident_type' => 'theft',
            'incident_date' => '2026-05-24 10:00:00',
            'incident_location' => 'Purok 1',
            'incident_narrative' => 'Stolen bicycle',
            'status' => 'filed',
            'filed_by' => $this->admin->id,
        ]);

        $response = $this->postJson('/api/v1/webhooks/blotter-status', [
            'blotter_id' => $blotter->id,
            'status' => 'under_investigation',
        ], [
            'X-Webhook-Secret' => 'barangay_n8n_secret_2026',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('blotter_records', [
            'id' => $blotter->id,
            'status' => 'under_investigation',
        ]);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_name' => 'inbound-blotter-status',
        ]);
    }

    /**
     * Test inbound announcement webhook.
     */
    public function test_inbound_announcement_webhook_dispatches_event(): void
    {
        Event::fake([AnnouncementPublished::class]);

        $announcement = Announcement::create([
            'posted_by' => $this->admin->id,
            'title' => 'Community Assembly',
            'content' => 'Meeting tonight',
            'is_barangay_wide' => true,
            'priority' => 'normal',
            'is_published' => false,
        ]);

        $response = $this->postJson('/api/v1/webhooks/announcement', [
            'announcement_id' => $announcement->id,
        ], [
            'X-Webhook-Secret' => 'barangay_n8n_secret_2026',
        ]);

        $response->assertStatus(200);

        Event::assertDispatched(AnnouncementPublished::class, function ($event) use ($announcement) {
            return $event->announcement->id === $announcement->id;
        });
    }

    /**
     * Test log result webhook.
     */
    public function test_log_result_webhook_records_log(): void
    {
        $response = $this->postJson('/api/v1/webhooks/log-result', [
            'workflow_name' => 'test-workflow-run',
            'status' => 'success',
            'payload' => ['key' => 'val'],
            'response' => 'Completed successfully',
        ], [
            'X-Webhook-Secret' => 'barangay_n8n_secret_2026',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_name' => 'test-workflow-run',
            'status' => 'success',
            'response' => 'Completed successfully',
        ]);
    }

    /**
     * Test outbound dispatch triggered by events.
     */
    public function test_events_trigger_outbound_n8n_webhook_dispatches(): void
    {
        Http::fake();

        $document = DocumentRequest::create([
            'resident_id' => $this->resident->id,
            'document_type' => 'clearance',
            'purpose' => 'Employment',
            'status' => 'pending',
            'amount' => 0.00,
            'is_paid' => false,
        ]);

        // Dispatch status changed event to trigger outbound n8n
        event(new DocumentStatusChanged($document, 'approved'));

        Http::assertSent(function ($request) {
            return $request->url() === 'http://localhost:5678/webhook/document-status' &&
                   $request->header('X-Webhook-Secret')[0] === 'barangay_n8n_secret_2026' &&
                   $request['status'] === 'approved';
        });

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_name' => 'document-status-notifier',
            'status' => 'success',
        ]);
    }

    /**
     * Test admin list and clear workflow logs endpoints.
     */
    public function test_admin_can_list_and_clear_workflow_logs(): void
    {
        WorkflowLog::create([
            'workflow_name' => 'some-run',
            'status' => 'success',
            'triggered_at' => now(),
        ]);

        // Unauthenticated user cannot list
        $response = $this->getJson('/api/v1/workflow-logs');
        $response->assertStatus(401);

        // Admin can list logs
        $response = $this->actingAs($this->admin)->getJson('/api/v1/workflow-logs');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'logs');

        // Admin can clear logs
        $response = $this->actingAs($this->admin)->deleteJson('/api/v1/workflow-logs/clear');
        $response->assertStatus(200);

        $this->assertDatabaseCount('workflow_logs', 0);
    }
}
