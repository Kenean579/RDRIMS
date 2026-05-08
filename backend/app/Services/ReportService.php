<?php

namespace App\Services;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function generate(string $reportName, array $parameters = []): Report
    {
        $html = $this->renderHtml($reportName, $parameters);
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

    private function renderHtml(string $reportName, array $parameters): string
    {
        return match($reportName) {
            'projects_summary' => view('reports.projects_summary', $parameters)->render(),
            'outputs_summary'  => view('reports.outputs_summary',  $parameters)->render(),
            default            => '<h1>No template found for ' . $reportName . '</h1>',
        };
    }
}