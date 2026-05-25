<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Events\AnnouncementPublished;
use App\Services\TargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\Laravel\Facades\Image;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    /**
     * Display active, published announcements for the public feed.
     */
    public function index(Request $request): JsonResponse
    {
        $now = Carbon::now();

        $query = Announcement::with(['author:id,full_name', 'targets.purok'])
            ->where('is_published', true)
            ->where('publish_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', $now);
            });

        // Optional priority filtering
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Optional search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $announcements = $query->latest('publish_date')->get();

        return response()->json([
            'announcements' => $announcements
        ]);
    }

    /**
     * Display all announcements (including drafts and expired) for administrators/staff.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Announcement::class);

        $query = Announcement::with(['author:id,full_name', 'targets.purok']);

        // Filter by status: draft, published, expired
        if ($request->filled('status')) {
            $status = $request->status;
            $now = Carbon::now();

            if ($status === 'draft') {
                $query->where('is_published', false);
            } elseif ($status === 'published') {
                $query->where('is_published', true)
                      ->where('publish_date', '<=', $now)
                      ->where(function ($q) use ($now) {
                          $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>', $now);
                      });
            } elseif ($status === 'expired') {
                $query->where('is_published', true)
                      ->whereNotNull('expiry_date')
                      ->where('expiry_date', '<=', $now);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $announcements = $query->latest()->paginate($request->input('per_page', 10));

        return response()->json([
            'announcements' => $announcements->items(),
            'meta' => [
                'current_page' => $announcements->currentPage(),
                'last_page'    => $announcements->lastPage(),
                'per_page'     => $announcements->perPage(),
                'total'        => $announcements->total(),
            ]
        ]);
    }

    /**
     * Retrieve details of a single announcement.
     */
    public function show(int $id): JsonResponse
    {
        $announcement = Announcement::with(['author:id,full_name', 'targets.purok'])->findOrFail($id);

        if (!$announcement->is_published) {
            Gate::authorize('view', $announcement);
        }

        return response()->json([
            'announcement' => $announcement
        ]);
    }

    /**
     * Store a newly created announcement (as draft or scheduled/published).
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Announcement::class);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'is_barangay_wide' => ['required', 'boolean'],
            'priority' => ['required', 'string', 'in:low,normal,high'],
            'publish_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'purok_ids' => ['nullable', 'array'],
            'purok_ids.*' => ['exists:puroks,id']
        ];

        // Custom validation check if not barangay wide
        if (!$request->boolean('is_barangay_wide')) {
            $rules['purok_ids'][] = 'required';
            $rules['purok_ids'][] = 'min:1';
        }

        $validated = $request->validate($rules);

        // Upload and scale image if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (extension_loaded('gd') || extension_loaded('imagick')) {
                $image = Image::read($file);
                $image->scaleDown(width: 800);
                $encoded = $image->toJpeg();
                $filename = 'announcements/ann_' . time() . '_' . uniqid() . '.jpg';
                Storage::disk('public')->put($filename, $encoded->toString());
                $imagePath = $filename;
            } else {
                // Fallback to storing raw file if GD is not available
                $imagePath = $file->store('announcements', 'public');
            }
        }

        $announcement = Announcement::create([
            'posted_by' => auth()->id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'is_barangay_wide' => $request->boolean('is_barangay_wide'),
            'priority' => $validated['priority'],
            'is_published' => $request->boolean('is_published', false),
            'publish_date' => isset($validated['publish_date']) ? Carbon::parse($validated['publish_date']) : null,
            'expiry_date' => isset($validated['expiry_date']) ? Carbon::parse($validated['expiry_date']) : null,
        ]);

        // Target Puroks if not barangay wide
        if (!$announcement->is_barangay_wide && !empty($validated['purok_ids'])) {
            foreach ($validated['purok_ids'] as $purokId) {
                AnnouncementTarget::create([
                    'announcement_id' => $announcement->id,
                    'purok_id' => $purokId
                ]);
            }
        }

        // If is_published is true right now, trigger event
        if ($announcement->is_published) {
            $targetService = app(TargetService::class);
            $targetResidentIds = $targetService->getResidentIds($announcement);
            event(new AnnouncementPublished($announcement, $targetResidentIds));
        }

        return response()->json([
            'message' => 'Announcement created successfully.',
            'announcement' => $announcement->load(['author:id,full_name', 'targets.purok'])
        ], 201);
    }

    /**
     * Update an announcement.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        Gate::authorize('update', $announcement);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'is_barangay_wide' => ['required', 'boolean'],
            'priority' => ['required', 'string', 'in:low,normal,high'],
            'publish_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'purok_ids' => ['nullable', 'array'],
            'purok_ids.*' => ['exists:puroks,id']
        ];

        if (!$request->boolean('is_barangay_wide')) {
            $rules['purok_ids'][] = 'required';
            $rules['purok_ids'][] = 'min:1';
        }

        $validated = $request->validate($rules);

        // Upload and scale image if present
        $imagePath = $announcement->image_path;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }

            $file = $request->file('image');
            if (extension_loaded('gd') || extension_loaded('imagick')) {
                $image = Image::read($file);
                $image->scaleDown(width: 800);
                $encoded = $image->toJpeg();
                $filename = 'announcements/ann_' . time() . '_' . uniqid() . '.jpg';
                Storage::disk('public')->put($filename, $encoded->toString());
                $imagePath = $filename;
            } else {
                $imagePath = $file->store('announcements', 'public');
            }
        }

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'is_barangay_wide' => $request->boolean('is_barangay_wide'),
            'priority' => $validated['priority'],
            'publish_date' => isset($validated['publish_date']) ? Carbon::parse($validated['publish_date']) : null,
            'expiry_date' => isset($validated['expiry_date']) ? Carbon::parse($validated['expiry_date']) : null,
        ]);

        // Sync Purok targets
        if ($announcement->is_barangay_wide) {
            $announcement->targets()->delete();
        } else {
            $announcement->targets()->delete();
            if (!empty($validated['purok_ids'])) {
                foreach ($validated['purok_ids'] as $purokId) {
                    AnnouncementTarget::create([
                        'announcement_id' => $announcement->id,
                        'purok_id' => $purokId
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Announcement updated successfully.',
            'announcement' => $announcement->fresh(['author:id,full_name', 'targets.purok'])
        ]);
    }

    /**
     * Soft delete an announcement.
     */
    public function destroy(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        Gate::authorize('delete', $announcement);

        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully.'
        ]);
    }

    /**
     * Publish an announcement draft and fire notifications.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        Gate::authorize('publish', $announcement);

        if ($announcement->is_published) {
            return response()->json([
                'message' => 'Announcement is already published.'
            ], 422);
        }

        $announcement->update([
            'is_published' => true,
            'publish_date' => $announcement->publish_date ?? Carbon::now()
        ]);

        // Resolve targets and trigger event
        $targetService = app(TargetService::class);
        $targetResidentIds = $targetService->getResidentIds($announcement);
        event(new AnnouncementPublished($announcement, $targetResidentIds));

        return response()->json([
            'message' => 'Announcement published successfully and broadcasts initiated.',
            'announcement' => $announcement->load(['author:id,full_name', 'targets.purok'])
        ]);
    }
}
