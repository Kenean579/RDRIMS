<?php

namespace App\Services;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function generate(string $reportName, string $reportType, array $parameters = []): Report
    {
        $html = $this->renderHtml($reportType, $parameters);
        $pdf  = Pdf::loadHTML($html);

        $fileName = 'reports/' . uniqid() . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        return Report::create([
            'name'         => $reportName,
            'file_path'    => $fileName,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
            'parameters'   => $parameters,
        ]);
    }

    private function renderHtml(string $reportType, array $parameters): string
    {
        return match($reportType) {
            'projects' => view('reports.projects', $parameters)->render(),
            default => throw new \InvalidArgumentException('Unsupported report type.'),
        };
    }
}
