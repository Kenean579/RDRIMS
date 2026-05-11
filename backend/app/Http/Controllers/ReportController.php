<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Report::class);
        $reports = Report::with('generatedBy')->latest()->paginate(20);
        return response()->json($reports);
    }

    public function generate(GenerateReportRequest $request): JsonResponse
    {
        $report = $this->reportService->generate(
            $request->input('report_name'),
            $request->input('parameters', [])
        );

        return response()->json($report, 201);
    }

    public function download(Report $report): mixed
    {
        $this->authorize('view', $report);

        if (!Storage::disk('public')->exists($report->file_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        return Storage::disk('public')->download($report->file_path, $report->name . '.pdf');
    }
}