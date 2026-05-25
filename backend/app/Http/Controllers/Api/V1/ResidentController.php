<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Http\Resources\ResidentResource;
use App\Models\Resident;
use App\Models\GroupInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ResidentController extends Controller
{
    /**
     * Display a listing of residents with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Resident::with(['purok', 'headOfHousehold', 'creator']);

        // Search name filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Purok filter
        if ($request->filled('purok_id')) {
            $query->where('purok_id', $request->purok_id);
        }

        // Demographic filters
        if ($request->has('is_voter')) {
            $query->where('is_voter', $request->boolean('is_voter'));
        }
        if ($request->has('is_indigent')) {
            $query->where('is_indigent', $request->boolean('is_indigent'));
        }
        if ($request->has('is_pwd')) {
            $query->where('is_pwd', $request->boolean('is_pwd'));
        }
        if ($request->has('is_senior_citizen')) {
            $query->where('is_senior_citizen', $request->boolean('is_senior_citizen'));
        }

        // Active filter (default: show active only unless specified)
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            $query->where('is_active', true);
        }

        $residents = $query->orderBy('last_name')
                           ->orderBy('first_name')
                           ->paginate($request->input('per_page', 10));

        return response()->json([
            'residents' => ResidentResource::collection($residents),
            'meta' => [
                'current_page' => $residents->currentPage(),
                'last_page' => $residents->lastPage(),
                'per_page' => $residents->perPage(),
                'total' => $residents->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resident.
     */
    public function store(StoreResidentRequest $request): JsonResponse
    {
        $resident = Resident::create(array_merge(
            $request->validated(),
            [
                'created_by' => auth()->id(),
                'is_active' => true,
            ]
        ));

        // Sync household group mapping
        $headId = $request->input('head_of_household_id', $resident->id);
        GroupInfo::updateOrCreate(
            ['resident_id' => $resident->id],
            ['head_of_household_id' => $headId ?: $resident->id]
        );

        event(new \App\Events\ResidentCreated($resident));

        return response()->json([
            'message' => 'Resident profile created successfully.',
            'resident' => new ResidentResource($resident->load(['purok', 'headOfHousehold', 'creator'])),
        ], 201);
    }

    /**
     * Display the specified resident profile.
     */
    public function show(int $id): JsonResponse
    {
        $resident = Resident::with(['purok', 'headOfHousehold', 'creator', 'householdMembers'])
                            ->findOrFail($id);

        return response()->json([
            'resident' => new ResidentResource($resident),
            'household_members' => ResidentResource::collection($resident->householdMembers),
        ]);
    }

    /**
     * Update the specified resident.
     */
    public function update(UpdateResidentRequest $request, int $id): JsonResponse
    {
        $resident = Resident::findOrFail($id);
        $resident->update($request->validated());

        // Sync household group mapping if updated
        if ($request->has('head_of_household_id') || $request->has('purok_id')) {
            $headId = $request->input('head_of_household_id', $resident->id);
            GroupInfo::updateOrCreate(
                ['resident_id' => $resident->id],
                ['head_of_household_id' => $headId ?: $resident->id]
            );
        }

        return response()->json([
            'message' => 'Resident profile updated successfully.',
            'resident' => new ResidentResource($resident->fresh(['purok', 'headOfHousehold', 'creator'])),
        ]);
    }

    /**
     * Soft delete/deactivate the specified resident.
     */
    public function destroy(int $id): JsonResponse
    {
        $resident = Resident::findOrFail($id);
        
        // Deactivate active flag
        $resident->update(['is_active' => false]);
        $resident->delete(); // Soft delete

        return response()->json([
            'message' => 'Resident profile deactivated and archived successfully.',
        ]);
    }

    /**
     * Upload and optimize resident photo.
     */
    public function uploadPhoto(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $resident = Resident::findOrFail($id);

        // Delete old photo if it exists
        if ($resident->photo_path) {
            Storage::disk('public')->delete($resident->photo_path);
        }

        $file = $request->file('photo');

        // Optimize and resize using Intervention Image
        $image = Image::read($file);
        $image->scaleDown(width: 400, height: 400); // Resize max boundaries keeping aspect ratio
        $encoded = $image->toJpeg(); // Encode to JPG format

        $filename = 'residents/photo_' . $resident->id . '_' . time() . '.jpg';
        
        // Save to public storage disk
        Storage::disk('public')->put($filename, $encoded->toString());

        $resident->update(['photo_path' => $filename]);

        return response()->json([
            'message' => 'Profile photo uploaded successfully.',
            'photo_url' => asset('storage/' . $filename),
            'resident' => new ResidentResource($resident->fresh(['purok', 'headOfHousehold'])),
        ]);
    }
}
