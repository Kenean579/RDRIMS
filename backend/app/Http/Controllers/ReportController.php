<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    public function index(): JsonResponse
    {
        $reports = Report::with('generatedBy')
            ->orderBy('generated_at', 'desc')
            ->paginate(20);

        return response()->json($reports);
    }

    public function generate(GenerateReportRequest $request): JsonResponse
    {
        $view = match ($request->type) {
            'projects' => 'reports.projects',
            'outputs' => 'reports.outputs',
            'publications' => 'reports.publications',
            'expenses' => 'reports.expenses',
            'community' => 'reports.community',
            default => abort(400, 'Invalid report type.'),
        };

        $filters = $request->parameters ?? [];
        $data = $this->getReportData($request->type, $filters);

        $report = $this->reportService->generate($request->name, $data);

        return response()->json($report, 201);
    }

    public function download(Report $report): mixed
    {
        $this->authorize('view', $report);

        if (! Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'Report file not found.');
        }

        return Storage::disk('local')->download($report->file_path);
    }

    private function getReportData(string $type, array $filters): array
    {
        return match ($type) {
            'projects' => ['projects' => \App\Models\Project::with('status', 'pi')->get()],
            'outputs' => ['outputs' => \App\Models\Output::with('status', 'category')->get()],
            'publications' => ['publications' => \App\Models\Publication::with('authors')->get()],
            'expenses' => ['expenses' => \App\Models\Expense::with('project')->get()],
            'community' => ['problems' => \App\Models\CommunityProblem::with('status')->get()],
            default => [],
        };
    }
}