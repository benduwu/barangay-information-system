<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\Purok;
use App\Models\Resident;
use App\Events\AnnouncementPublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $purok;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $this->admin = User::create([
            'username' => 'testadmin',
            'email' => 'admin@test.com',
            'full_name' => 'Test Admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');

        $this->staff = User::create([
            'username' => 'teststaff',
            'email' => 'staff@test.com',
            'full_name' => 'Test Staff',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->staff->assignRole('staff');

        $this->purok = Purok::create([
            'purok_name' => 'Purok Uno',
            'description' => 'First Purok'
        ]);
    }

    public function test_public_can_view_published_and_active_announcements_only()
    {
        // 1. Published active
        $activeAnn = Announcement::create([
            'posted_by' => $this->admin->id,
            'title' => 'Active Announcement',
            'content' => 'This is active',
            'is_published' => true,
            'publish_date' => now()->subDay(),
            'is_barangay_wide' => true,
            'priority' => 'normal',
        ]);

        // 2. Draft
        $draftAnn = Announcement::create([
            'posted_by' => $this->admin->id,
            'title' => 'Draft Announcement',
            'content' => 'This is draft',
            'is_published' => false,
            'is_barangay_wide' => true,
            'priority' => 'normal',
        ]);

        // 3. Expired
        $expiredAnn = Announcement::create([
            'posted_by' => $this->admin->id,
            'title' => 'Expired Announcement',
            'content' => 'This is expired',
            'is_published' => true,
            'publish_date' => now()->subDays(5),
            'expiry_date' => now()->subDay(),
            'is_barangay_wide' => true,
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/announcements');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'announcements')
            ->assertJsonPath('announcements.0.id', $activeAnn->id);
    }

    public function test_admin_can_view_all_announcements()
    {
        Announcement::create([
            'posted_by' => $this->admin->id,
            'title' => 'Active',
            'content' => 'Active content',
            'is_published' => true,
            'publish_date' => now()->subDay(),
            'is_barangay_wide' => true,
            'priority' => 'normal',
        ]);

        Announcement::create([
            'posted_by' => $this->admin->id,
            'title' => 'Draft',
            'content' => 'Draft content',
            'is_published' => false,
            'is_barangay_wide' => true,
            'priority' => 'normal',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/announcements');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'announcements');
    }

    public function test_admin_can_create_announcement_with_targets_and_image()
    {
        Storage::fake('public');
        Event::fake();

        $file = UploadedFile::fake()->create('announcement.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/announcements', [
                'title' => 'New Announcement',
                'content' => 'This is content',
                'image' => $file,
                'is_barangay_wide' => false,
                'priority' => 'high',
                'purok_ids' => [$this->purok->id],
                'is_published' => true,
                'publish_date' => now()->toIso8601String(),
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('announcements', [
            'title' => 'New Announcement',
            'is_barangay_wide' => false,
            'priority' => 'high',
            'is_published' => true,
        ]);

        $announcement = Announcement::first();
        $this->assertNotNull($announcement->image_path);
        Storage::disk('public')->assertExists($announcement->image_path);

        $this->assertDatabaseHas('announcement_targets', [
            'announcement_id' => $announcement->id,
            'purok_id' => $this->purok->id,
        ]);

        Event::assertDispatched(AnnouncementPublished::class);
    }

    public function test_admin_can_publish_draft_which_dispatches_event()
    {
        Event::fake();

        $draft = Announcement::create([
            'posted_by' => $this->admin->id,
            'title' => 'Draft Announcement',
            'content' => 'Content here',
            'is_published' => false,
            'is_barangay_wide' => true,
            'priority' => 'normal',
        ]);

        // Create an active resident so TargetService resolves them
        Resident::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'civil_status' => 'Single',
            'purok_id' => $this->purok->id,
            'contact_number' => '09123456789',
            'email' => 'juan@example.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/admin/announcements/{$draft->id}/publish");

        $response->assertStatus(200);
        $this->assertTrue($draft->fresh()->is_published);
        $this->assertNotNull($draft->fresh()->publish_date);

        Event::assertDispatched(AnnouncementPublished::class, function ($event) {
            return count($event->targetResidentIds) === 1;
        });
    }
}
