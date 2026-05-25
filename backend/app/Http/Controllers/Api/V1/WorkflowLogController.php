<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WorkflowLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowLogController extends Controller
{
    /**
     * Display a listing of the workflow logs (Admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $query = WorkflowLog::query();

        // Search by workflow name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('workflow_name', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy('triggered_at', 'desc')
                      ->paginate($request->input('per_page', 15));

        return response()->json([
            'logs' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ]
        ]);
    }

    /**
     * Clear all workflow logs (Admin only).
     */
    public function clear(): JsonResponse
    {
        WorkflowLog::truncate();

        return response()->json([
            'message' => 'All workflow logs cleared successfully.'
        ]);
    }
}
