<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\DocumentRequest;
use App\Events\DocumentStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    /**
     * Display a listing of document requests.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DocumentRequest::with(['resident', 'processor']);

        // Search by resident name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('resident', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Filter by document type
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->orderBy('created_at', 'desc')
                           ->paginate($request->input('per_page', 10));

        return response()->json([
            'documents' => DocumentResource::collection($documents),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
            ],
        ]);
    }

    /**
     * Store a newly created document request.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = DocumentRequest::create(array_merge(
            $request->validated(),
            [
                'status' => 'pending',
                'amount' => 0.00,
                'is_paid' => false,
            ]
        ));

        // Note: Event is not typically fired on pending creation, but we can do it if desired.
        // Let's fire it just in case so automation can track any status.
        event(new DocumentStatusChanged($document, 'pending'));

        return response()->json([
            'message' => 'Document request submitted successfully.',
            'document' => new DocumentResource($document->load(['resident'])),
        ], 201);
    }

    /**
     * Display the specified document request.
     */
    public function show(int $id): JsonResponse
    {
        $document = DocumentRequest::with(['resident', 'processor'])->findOrFail($id);

        return response()->json([
            'document' => new DocumentResource($document),
        ]);
    }

    /**
     * Approve the document request.
     */
    public function approve(int $id): JsonResponse
    {
        $document = DocumentRequest::findOrFail($id);

        if ($document->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending document requests can be approved.',
            ], 422);
        }

        $document->update([
            'status' => 'approved',
            'processed_by' => auth()->id(),
        ]);

        event(new DocumentStatusChanged($document, 'approved'));

        return response()->json([
            'message' => 'Document request approved successfully.',
            'document' => new DocumentResource($document->load(['resident', 'processor'])),
        ]);
    }

    /**
     * Reject the document request.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $document = DocumentRequest::findOrFail($id);

        if ($document->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending document requests can be rejected.',
            ], 422);
        }

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'processed_by' => auth()->id(),
        ]);

        event(new DocumentStatusChanged($document, 'rejected'));

        return response()->json([
            'message' => 'Document request rejected successfully.',
            'document' => new DocumentResource($document->load(['resident', 'processor'])),
        ]);
    }

    /**
     * Release the approved document request and process payment.
     */
    public function release(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'official_receipt_no' => ['required', 'string', 'max:100'],
        ]);

        $document = DocumentRequest::findOrFail($id);

        if ($document->status !== 'approved') {
            return response()->json([
                'message' => 'Only approved document requests can be released.',
            ], 422);
        }

        $document->update([
            'status' => 'released',
            'amount' => $request->amount,
            'official_receipt_no' => $request->official_receipt_no,
            'is_paid' => true,
            'issued_date' => now(),
            'valid_until' => now()->addMonths(6),
            'processed_by' => auth()->id(),
        ]);

        event(new DocumentStatusChanged($document, 'released'));

        return response()->json([
            'message' => 'Document request released successfully.',
            'document' => new DocumentResource($document->load(['resident', 'processor'])),
        ]);
    }

    /**
     * Generate and stream a PDF of the document.
     */
    public function pdf(Request $request, int $id)
    {
        // Handle token parameter manually for iframe/pdf generation authentication
        if ($request->has('token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->query('token'));
        }

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $document = DocumentRequest::with(['resident', 'processor'])->findOrFail($id);

        if ($document->status !== 'released' && $document->status !== 'approved') {
            return response()->json([
                'message' => 'Document can only be printed if it has been approved or released.',
            ], 403);
        }

        // Set up PDF options or view data
        $data = [
            'document' => $document,
            'resident' => $document->resident,
            'issued_date' => $document->issued_date ?? now(),
            'valid_until' => $document->valid_until ?? now()->addMonths(6),
        ];

        $pdf = Pdf::loadView('pdf.certificate', $data);

        return $pdf->stream("{$document->control_number}.pdf");
    }
}
