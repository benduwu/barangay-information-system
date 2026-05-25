<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Purok;
use App\Models\DocumentRequest;
use App\Models\BlotterRecord;
use App\Models\ReportLog;
use App\Exports\ResidentExport;
use App\Exports\BlotterExport;
use App\Exports\MonthlyExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * GET /api/v1/reports/residents
     * Aggregate resident count by purok, age groups, and gender.
     */
    public function residents(Request $request)
    {
        $now = Carbon::now();

        // 1. Total & Gender Count
        $totalResidents = Resident::count();
        $maleCount = Resident::where('gender', 'male')->count();
        $femaleCount = Resident::where('gender', 'female')->count();

        // 2. Age group breakdowns
        $seniorCount = Resident::where('date_of_birth', '<=', $now->copy()->subYears(60))->count();
        $adultCount = Resident::whereBetween('date_of_birth', [
            $now->copy()->subYears(59)->startOfDay(),
            $now->copy()->subYears(18)->endOfDay()
        ])->count();
        $youthCount = Resident::whereBetween('date_of_birth', [
            $now->copy()->subYears(17)->startOfDay(),
            $now->copy()->subYears(12)->endOfDay()
        ])->count();
        $childCount = Resident::where('date_of_birth', '>', $now->copy()->subYears(12)->startOfDay())->count();

        // 3. Resident count by Purok
        $purokBreakdown = Purok::withCount('residents')->get();

        return response()->json([
            'summary' => [
                'total_residents' => $totalResidents,
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
                'senior_count' => $seniorCount,
                'adult_count' => $adultCount,
                'youth_count' => $youthCount,
                'child_count' => $childCount,
            ],
            'purok_breakdown' => $purokBreakdown
        ]);
    }

    /**
     * GET /api/v1/reports/documents
     * Aggregate document issuances by type, status, and custom date range.
     */
    public function documents(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        $query = DocumentRequest::whereBetween('created_at', [$startDate, $endDate]);

        // 1. Counts by Type
        $typeBreakdown = (clone $query)
            ->select('document_type', DB::raw('count(*) as count'))
            ->groupBy('document_type')
            ->get();

        // 2. Counts by Status
        $statusBreakdown = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // 3. Revenue generated
        $revenue = DocumentRequest::where('status', 'released')
            ->whereBetween('issued_date', [$startDate, $endDate])
            ->sum('amount');

        return response()->json([
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'type_breakdown' => $typeBreakdown,
            'status_breakdown' => $statusBreakdown,
            'revenue' => (float)$revenue,
            'total_requests' => (clone $query)->count()
        ]);
    }

    /**
     * GET /api/v1/reports/blotters
     * Aggregate blotters count by status, type, and monthly trends inside a range.
     */
    public function blotters(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfYear();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfYear();

        $query = BlotterRecord::whereBetween('incident_date', [$startDate, $endDate]);

        // 1. Status Breakdown
        $statusBreakdown = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // 2. Incident Type Breakdown
        $typeBreakdown = (clone $query)
            ->select('incident_type', DB::raw('count(*) as count'))
            ->groupBy('incident_type')
            ->get();

        // 3. Monthly Filing Trends
        // Raw sqlite month grouping or fallback
        $monthlyTrend = (clone $query)
            ->select(DB::raw("strftime('%Y-%m', incident_date) as month"), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'status_breakdown' => $statusBreakdown,
            'type_breakdown' => $typeBreakdown,
            'monthly_trend' => $monthlyTrend,
            'total_cases' => (clone $query)->count()
        ]);
    }

    /**
     * GET /api/v1/reports/monthly
     * Combined monthly activity summary.
     */
    public function monthly(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        // New Residents Enrolled
        $newResidents = Resident::whereBetween('created_at', [$startDate, $endDate])->count();

        // Documents Processed (Released)
        $docsProcessed = DocumentRequest::where('status', 'released')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        // Revenue Collected
        $revenue = DocumentRequest::where('status', 'released')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('amount');

        // Blotter incidents filed vs settled
        $blottersFiled = BlotterRecord::whereBetween('created_at', [$startDate, $endDate])->count();
        $blottersSettled = BlotterRecord::where('status', 'settled')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $blottersEscalated = BlotterRecord::where('status', 'escalated')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        // Purok Statistics inside Period
        $puroks = Purok::all();
        $purokStats = [];
        foreach ($puroks as $purok) {
            $purokStats[] = [
                'name' => $purok->purok_name,
                'new_count' => Resident::where('purok_id', $purok->id)->whereBetween('created_at', [$startDate, $endDate])->count(),
                'total_count' => Resident::where('purok_id', $purok->id)->count()
            ];
        }

        // Documents Statistics inside Period
        $docTypes = ['clearance', 'residency', 'indigency'];
        $docStats = [];
        foreach ($docTypes as $type) {
            $docStats[] = [
                'type' => $type,
                'filed' => DocumentRequest::where('document_type', $type)->whereBetween('created_at', [$startDate, $endDate])->count(),
                'approved' => DocumentRequest::where('document_type', $type)->where('status', 'approved')->whereBetween('updated_at', [$startDate, $endDate])->count(),
                'released' => DocumentRequest::where('document_type', $type)->where('status', 'released')->whereBetween('updated_at', [$startDate, $endDate])->count(),
                'revenue' => (float)DocumentRequest::where('document_type', $type)->where('status', 'released')->whereBetween('updated_at', [$startDate, $endDate])->sum('amount')
            ];
        }

        return response()->json([
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'new_residents' => $newResidents,
                'documents_processed' => $docsProcessed,
                'revenue_generated' => (float)$revenue,
                'blotters_filed' => $blottersFiled,
                'blotters_settled' => $blottersSettled,
                'blotters_escalated' => $blottersEscalated
            ],
            'purok_stats' => $purokStats,
            'document_stats' => $docStats
        ]);
    }

    /**
     * POST /api/v1/reports/export/pdf
     * Generate report PDF via Dompdf, log to report_logs.
     */
    public function exportPdf(Request $request)
    {
        // Admin role restriction for exports
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden. Only administrators can generate report exports.'], 403);
        }

        $request->validate([
            'report_type' => 'required|string|in:residents,blotters,monthly',
        ]);

        $type = $request->report_type;
        $now = Carbon::now();
        $generatedBy = auth()->user();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        $data = [
            'generated_at' => $now->toDateTimeString(),
            'generated_by_name' => $generatedBy->name,
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]
        ];

        $viewName = '';

        if ($type === 'residents') {
            $viewName = 'reports.report_resident';
            $data['residents'] = Resident::with('purok')->get();
            
            // Re-use aggregation queries for header summary
            $data['summary'] = [
                'total_residents' => Resident::count(),
                'male_count' => Resident::where('gender', 'male')->count(),
                'female_count' => Resident::where('gender', 'female')->count(),
                'senior_count' => Resident::where('date_of_birth', '<=', $now->copy()->subYears(60))->count(),
                'adult_count' => Resident::whereBetween('date_of_birth', [
                    $now->copy()->subYears(59)->startOfDay(),
                    $now->copy()->subYears(18)->endOfDay()
                ])->count(),
                'youth_count' => Resident::whereBetween('date_of_birth', [
                    $now->copy()->subYears(17)->startOfDay(),
                    $now->copy()->subYears(12)->endOfDay()
                ])->count(),
                'child_count' => Resident::where('date_of_birth', '>', $now->copy()->subYears(12)->startOfDay())->count(),
            ];
            $data['purok_breakdown'] = Purok::withCount('residents')->get();

        } elseif ($type === 'blotters') {
            $viewName = 'reports.report_blotter';
            $data['blotters'] = BlotterRecord::with(['parties', 'officer', 'creator'])
                ->whereBetween('incident_date', [$startDate, $endDate])
                ->get();
            
            $data['summary'] = [
                'total_blotters' => count($data['blotters']),
                'filed_count' => BlotterRecord::where('status', 'filed')->whereBetween('incident_date', [$startDate, $endDate])->count(),
                'investigation_count' => BlotterRecord::where('status', 'under_investigation')->whereBetween('incident_date', [$startDate, $endDate])->count(),
                'settled_count' => BlotterRecord::where('status', 'settled')->whereBetween('incident_date', [$startDate, $endDate])->count(),
                'escalated_count' => BlotterRecord::where('status', 'escalated')->whereBetween('incident_date', [$startDate, $endDate])->count(),
            ];
            $data['type_breakdown'] = BlotterRecord::whereBetween('incident_date', [$startDate, $endDate])
                ->select('incident_type', DB::raw('count(*) as aggregate_count'))
                ->groupBy('incident_type')
                ->get();

        } else {
            // monthly combined report
            $viewName = 'reports.report_monthly';
            $monthlyResponse = $this->monthly($request)->getData(true);
            $data['summary'] = $monthlyResponse['summary'];
            $data['purok_stats'] = $monthlyResponse['purok_stats'];
            $data['document_stats'] = $monthlyResponse['document_stats'];
        }

        // Render PDF
        $pdf = Pdf::loadView($viewName, $data);
        
        // Save file to storage for log record
        $fileName = 'report_' . $type . '_' . time() . '.pdf';
        $directory = 'reports';
        
        // Ensure directories exist
        Storage::disk('public')->makeDirectory($directory);
        $filePath = $directory . '/' . $fileName;
        
        Storage::disk('public')->put($filePath, $pdf->output());

        // Create log record
        ReportLog::create([
            'generated_by' => $generatedBy->id,
            'report_type' => $type,
            'parameters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'file_path' => $filePath,
            'generated_at' => $now,
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * POST /api/v1/reports/export/excel
     * Generate Excel via Maatwebsite Excel, log to report_logs.
     */
    public function exportExcel(Request $request)
    {
        // Admin role restriction for exports
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden. Only administrators can generate report exports.'], 403);
        }

        $request->validate([
            'report_type' => 'required|string|in:residents,blotters,monthly',
        ]);

        $type = $request->report_type;
        $now = Carbon::now();
        $generatedBy = auth()->user();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        $fileName = 'report_' . $type . '_' . time() . '.xlsx';
        $directory = 'reports';
        
        Storage::disk('public')->makeDirectory($directory);
        $filePath = $directory . '/' . $fileName;

        $export = null;

        if ($type === 'residents') {
            $residents = Resident::with('purok')->get();
            $export = new ResidentExport($residents);

        } elseif ($type === 'blotters') {
            $blotters = BlotterRecord::with(['parties', 'officer', 'creator'])
                ->whereBetween('incident_date', [$startDate, $endDate])
                ->get();
            $export = new BlotterExport($blotters);

        } else {
            // consolidated monthly report list representation
            $monthlyResponse = $this->monthly($request)->getData(true);
            $summary = $monthlyResponse['summary'];
            
            $formattedData = [
                ['category' => 'Consolidated Summary', 'indicator' => 'New Residents Enrolled', 'value' => $summary['new_residents']],
                ['category' => 'Consolidated Summary', 'indicator' => 'Certificates Issued', 'value' => $summary['documents_processed']],
                ['category' => 'Consolidated Summary', 'indicator' => 'Revenue Collected (Php)', 'value' => $summary['revenue_generated']],
                ['category' => 'Consolidated Summary', 'indicator' => 'Blotters Filed', 'value' => $summary['blotters_filed']],
                ['category' => 'Consolidated Summary', 'indicator' => 'Blotters Settled', 'value' => $summary['blotters_settled']],
                ['category' => 'Consolidated Summary', 'indicator' => 'Blotters Escalated', 'value' => $summary['blotters_escalated']],
            ];

            foreach ($monthlyResponse['purok_stats'] as $purok) {
                $formattedData[] = [
                    'category' => 'Resident Registrations by Purok',
                    'indicator' => $purok['name'] . ' (New Enrolments)',
                    'value' => $purok['new_count']
                ];
                $formattedData[] = [
                    'category' => 'Resident Registrations by Purok',
                    'indicator' => $purok['name'] . ' (Total Size)',
                    'value' => $purok['total_count']
                ];
            }

            foreach ($monthlyResponse['document_stats'] as $doc) {
                $formattedData[] = [
                    'category' => 'Clearances by Type (' . ucfirst($doc['type']) . ')',
                    'indicator' => ucfirst($doc['type']) . ' Filed Requests',
                    'value' => $doc['filed']
                ];
                $formattedData[] = [
                    'category' => 'Clearances by Type (' . ucfirst($doc['type']) . ')',
                    'indicator' => ucfirst($doc['type']) . ' Approved Requests',
                    'value' => $doc['approved']
                ];
                $formattedData[] = [
                    'category' => 'Clearances by Type (' . ucfirst($doc['type']) . ')',
                    'indicator' => ucfirst($doc['type']) . ' Released Requests',
                    'value' => $doc['released']
                ];
                $formattedData[] = [
                    'category' => 'Clearances by Type (' . ucfirst($doc['type']) . ')',
                    'indicator' => ucfirst($doc['type']) . ' Total Revenue Collected (Php)',
                    'value' => $doc['revenue']
                ];
            }

            $export = new MonthlyExport($formattedData);
        }

        // Store Excel in public storage path
        Excel::store($export, $filePath, 'public');

        // Log record
        ReportLog::create([
            'generated_by' => $generatedBy->id,
            'report_type' => $type,
            'parameters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'file_path' => $filePath,
            'generated_at' => $now,
        ]);

        return response()->download(storage_path('app/public/' . $filePath), $fileName);
    }
}
