<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurokResource;
use App\Models\Purok;
use Illuminate\Http\JsonResponse;

class PurokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $puroks = Purok::orderBy('purok_name')->get();

        return response()->json([
            'puroks' => PurokResource::collection($puroks),
        ]);
    }
}
