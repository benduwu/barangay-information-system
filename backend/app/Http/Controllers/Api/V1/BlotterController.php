<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlotterRequest;
use App\Http\Resources\BlotterResource;
use App\Models\BlotterRecord;
use App\Models\BlotterParty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlotterController extends Controller
{
    /**
     * Display a listing of blotter records.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlotterRecord::with(['parties', 'officer', 'creator']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('blotter_number', 'like', "%{$search}%")
                  ->orWhere('incident_type', 'like', "%{$search}%")
                  ->orWhere('incident_location', 'like', "%{$search}%")
                  ->orWhereHas('parties', function ($qp) use ($search) {
                      $qp->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Incident Date range filter
        if ($request->filled('date_start')) {
            $query->whereDate('incident_date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('incident_date', '<=', $request->date_end);
        }

        $records = $query->orderBy('created_at', 'desc')
                         ->paginate($request->input('per_page', 10));

        return response()->json([
            'blotters' => BlotterResource::collection($records),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
        ]);
    }

    /**
     * Store a newly created blotter record.
     */
    public function store(StoreBlotterRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request) {
            // Save main blotter record
            $blotter = BlotterRecord::create(array_merge(
                $request->safe()->except('parties'),
                [
                    'status' => 'filed',
                    'filed_by' => auth()->id(),
                ]
            ));

            // Create parties if provided
            if ($request->filled('parties')) {
                foreach ($request->input('parties') as $party) {
                    $blotter->parties()->create($party);
                }
            }

            // Record initial log to blotter_updates
            $blotter->updates()->create([
                'updated_by' => auth()->id(),
                'previous_status' => 'none',
                'new_status' => 'filed',
                'notes' => 'Blotter record initially filed and registered.',
            ]);

            return $blotter;
        });

        return response()->json([
            'message' => 'Blotter / incident record filed successfully.',
            'blotter' => new BlotterResource($record->load(['parties', 'officer', 'creator', 'updates'])),
        ], 201);
    }

    /**
     * Display the specified blotter record.
     */
    public function show(int $id): JsonResponse
    {
        $record = BlotterRecord::with(['parties', 'updates.updater', 'officer', 'creator'])->findOrFail($id);

        return response()->json([
            'blotter' => new BlotterResource($record),
        ]);
    }

    /**
     * Update the status of the blotter record.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:filed,under_investigation,settled,escalated'],
            'notes' => ['required', 'string', 'max:500'],
            'settlement_details' => ['nullable', 'required_if:status,settled', 'string'],
        ]);

        $record = BlotterRecord::findOrFail($id);
        $oldStatus = $record->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json([
                'message' => 'The blotter case is already marked as ' . $newStatus . '.',
            ], 422);
        }

        DB::transaction(function () use ($record, $request, $oldStatus, $newStatus) {
            // Update main status and settlement details
            $record->update([
                'status' => $newStatus,
                'settlement_details' => $newStatus === 'settled' ? $request->settlement_details : $record->settlement_details,
            ]);

            // Add history log trail
            $record->updates()->create([
                'updated_by' => auth()->id(),
                'previous_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => $request->notes,
            ]);
        });

        return response()->json([
            'message' => 'Blotter status updated successfully.',
            'blotter' => new BlotterResource($record->load(['parties', 'updates.updater', 'officer', 'creator'])),
        ]);
    }

    /**
     * Add a party to the blotter record.
     */
    public function addParty(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'resident_id' => ['nullable', 'exists:residents,id'],
            'role' => ['required', 'string', 'in:complainant,respondent,witness'],
            'full_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'statement' => ['nullable', 'string'],
        ]);

        $record = BlotterRecord::findOrFail($id);

        $party = $record->parties()->create($validated);

        return response()->json([
            'message' => 'Party member added successfully.',
            'party' => $party,
            'blotter' => new BlotterResource($record->load(['parties', 'updates.updater', 'officer', 'creator'])),
        ]);
    }

    /**
     * Assign an officer/investigator to the blotter record.
     */
    public function assignOfficer(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'assigned_officer_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $record = BlotterRecord::findOrFail($id);

        $record->update([
            'assigned_officer_id' => $request->assigned_officer_id,
        ]);

        // Log this assignment action in the updates history trail
        $record->updates()->create([
            'updated_by' => auth()->id(),
            'previous_status' => $record->status,
            'new_status' => $record->status,
            'notes' => $request->notes ?: 'Investigating officer assigned/updated.',
        ]);

        return response()->json([
            'message' => 'Investigating officer assigned successfully.',
            'blotter' => new BlotterResource($record->load(['parties', 'updates.updater', 'officer', 'creator'])),
        ]);
    }
}
