<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitReviewRequest;
use App\Models\Proposal;
use App\Models\ProposalReviewer;
use App\Models\ReviewCriterion;
use App\Models\ReviewDecision;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReviewerProposalController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('super_admin', 'research_admin')) {
            $assignments = ProposalReviewer::with(['proposal.status', 'proposal.type', 'reviewer'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $transformed = $assignments->getCollection()->map(function ($pivot) {
                $proposal = $pivot->proposal;
                $proposal->reviewPivot = clone $pivot;
                unset($proposal->reviewPivot->proposal);
                $proposal->review_progress = $this->reviewService->getProposalReviewProgress($proposal);
                return $proposal;
            });
            $assignments->setCollection($transformed);

            return response()->json($assignments);
        }

        $proposals = $user->reviewedProposals()->with('status', 'type')->paginate(20);
        return response()->json($proposals);
    }

    public function show(Proposal $proposal, Request $request): JsonResponse
    {
        $pivot = $this->reviewService->getAssignment($proposal, $request->user()->id);

        $proposal->load('status', 'type', 'file');
        $proposal->setRelation('submittedBy', null);
        $proposal->setRelation('investigators', collect([]));
        $proposal->submitted_by = null;

        $pivot->load('scores', 'decision');
        $proposal->reviewPivot = $pivot;
        $proposal->is_locked = $pivot->submitted_at !== null;

        return response()->json($proposal);
    }

    public function storeReview(SubmitReviewRequest $request, Proposal $proposal): JsonResponse
    {
        $pivot = $this->reviewService->getAssignment($proposal, $request->user()->id);
        $this->reviewService->assertNotLocked($pivot);

        $this->reviewService->submitReview(
            $pivot,
            $request->scores,
            (float) $request->overall_score,
            $request->overall_comments,
            (int) $request->decision_id
        );

        $this->reviewService->logAction('reviewer_submit_review', $proposal, $request->user(), [
            'proposal_reviewer_id' => $pivot->id,
            'overall_score' => $request->overall_score,
            'decision_id' => $request->decision_id,
        ]);

        return response()->json(['message' => 'Review submitted.']);
    }

    public function downloadTemplate(Proposal $proposal, Request $request): StreamedResponse|JsonResponse
    {
        $this->reviewService->getAssignment($proposal, $request->user()->id);

        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Review Template');

            $sheet->setCellValue('A1', 'Criterion ID');
            $sheet->setCellValue('B1', 'Criterion Name');
            $sheet->setCellValue('C1', 'Max Score');
            $sheet->setCellValue('D1', 'Score (Your Input)');
            $sheet->setCellValue('E1', 'Comments');

            $sheet->setCellValue('G1', 'Proposal ID');
            $sheet->setCellValue('H1', 'Reviewer ID');
            $sheet->setCellValue('G2', $proposal->id);
            $sheet->setCellValue('H2', $request->user()->id);

            $criteria = ReviewCriterion::where('is_active', true)->get();
            $row = 2;
            foreach ($criteria as $c) {
                $sheet->setCellValue('A' . $row, $c->id);
                $sheet->setCellValue('B' . $row, $c->name);
                $sheet->setCellValue('C' . $row, $c->max_score);
                $row++;
            }

            $row += 2;
            $sheet->setCellValue('A' . $row, 'Overall Decision');
            $sheet->setCellValue('B' . $row, 'Your Decision ID Input ->');

            $row += 2;
            $sheet->setCellValue('A' . $row, '--- Available Decisions (Do not edit below) ---');
            $row++;

            $decisions = ReviewDecision::all();
            foreach ($decisions as $d) {
                $sheet->setCellValue('A' . $row, $d->id);
                $sheet->setCellValue('B' . $row, $d->name);
                $row++;
            }

            $response = new StreamedResponse(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            });

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename="Review_Template_Proposal_' . $proposal->id . '.xlsx"');
            $response->headers->set('Cache-Control', 'max-age=0');

            $this->reviewService->logAction('reviewer_download_template', $proposal, $request->user());

            return $response;
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to generate review template.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ], 500);
        }
    }

    public function importReview(Request $request, Proposal $proposal): JsonResponse
    {
        $pivot = $this->reviewService->getAssignment($proposal, $request->user()->id);
        $this->reviewService->assertNotLocked($pivot);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $excelProposalId = $sheet->getCell('G2')->getValue();
            $excelReviewerId = $sheet->getCell('H2')->getValue();

            if ($excelProposalId != $proposal->id || $excelReviewerId != $request->user()->id) {
                throw ValidationException::withMessages([
                    'file' => ['The uploaded file does not match this proposal assignment.'],
                ]);
            }

            $row = 2;
            $totalReceived = 0;
            $totalMax = 0;
            $scoresData = [];

            while ($sheet->getCell('A' . $row)->getValue() && is_numeric($sheet->getCell('A' . $row)->getValue())) {
                $criterionId = $sheet->getCell('A' . $row)->getValue();
                $maxScore = $sheet->getCell('C' . $row)->getValue();
                $receivedScore = $sheet->getCell('D' . $row)->getValue();
                $comments = $sheet->getCell('E' . $row)->getValue();

                if (is_numeric($receivedScore)) {
                    $totalReceived += $receivedScore;
                    $totalMax += $maxScore;
                    $scoresData[] = [
                        'criterion_id' => (int) $criterionId,
                        'score' => $receivedScore,
                        'comments' => $comments,
                    ];
                }
                $row++;
            }

            $decisionId = null;
            $overallComments = '';

            for ($i = 1; $i <= 200; $i++) {
                $cellA = trim((string) $sheet->getCell('A' . $i)->getValue());
                $cellB = trim((string) $sheet->getCell('B' . $i)->getValue());

                if (stripos($cellA, 'Overall Decision') !== false || stripos($cellB, 'Decision ID Input') !== false) {
                    $decisionId = $sheet->getCell('C' . $i)->getValue();
                    $overallComments = $sheet->getCell('E' . $i)->getValue();

                    if (!$decisionId) {
                        $decisionId = $sheet->getCell('B' . ($i + 1))->getValue()
                            ?? $sheet->getCell('C' . ($i + 1))->getValue()
                            ?? $sheet->getCell('D' . ($i + 1))->getValue();

                        $overallComments = $overallComments ?: $sheet->getCell('E' . ($i + 1))->getValue();
                    }

                    if ($decisionId) {
                        break;
                    }
                }
            }

            if (!$decisionId) {
                for ($i = 1; $i <= 200; $i++) {
                    for ($col = 'A'; $col <= 'E'; $col++) {
                        $val = trim((string) $sheet->getCell($col . $i)->getValue());
                        if (stripos($val, 'Decision ID Input') !== false) {
                            $nextCol = chr(ord($col) + 1);
                            $decisionId = $sheet->getCell($nextCol . $i)->getValue() ?? $sheet->getCell($col . ($i + 1))->getValue();
                            if ($decisionId) {
                                break 2;
                            }
                        }
                    }
                }
            }

            if ($decisionId && !is_numeric($decisionId)) {
                $decisionName = strtolower(trim((string) $decisionId));
                $decision = ReviewDecision::whereRaw('LOWER(name) = ?', [$decisionName])
                    ->orWhereRaw('LOWER(name) LIKE ?', ["%{$decisionName}%"])
                    ->first();
                if ($decision) {
                    $decisionId = $decision->id;
                }
            }

            if (!$decisionId) {
                throw ValidationException::withMessages([
                    'file' => ['Overall Decision ID not found in Excel.'],
                ]);
            }

            if (!ReviewDecision::where('id', $decisionId)->exists()) {
                throw ValidationException::withMessages([
                    'file' => ['Invalid Overall Decision provided in Excel.'],
                ]);
            }

            $validCriterionIds = ReviewCriterion::pluck('id')->toArray();
            foreach ($scoresData as $sd) {
                if (!in_array($sd['criterion_id'], $validCriterionIds)) {
                    throw ValidationException::withMessages([
                        'file' => ["Invalid Criterion ID found in Excel: {$sd['criterion_id']}"],
                    ]);
                }
            }

            $this->reviewService->validateScores($scoresData);

            $overallScore = $totalMax > 0 ? ($totalReceived / $totalMax) * 5 : 0;

            $this->reviewService->submitReview(
                $pivot,
                $scoresData,
                $overallScore,
                $overallComments,
                (int) $decisionId
            );

            $this->reviewService->logAction('reviewer_upload_review', $proposal, $request->user(), [
                'proposal_reviewer_id' => $pivot->id,
                'overall_score' => $overallScore,
                'decision_id' => (int) $decisionId,
            ]);

            return response()->json([
                'message' => 'Excel review imported successfully.',
                'overall_score' => $overallScore,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (ReaderException $e) {
            return response()->json([
                'message' => 'Invalid file format. Please upload a valid Excel file.',
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to import review file.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ], 500);
        }
    }
}
