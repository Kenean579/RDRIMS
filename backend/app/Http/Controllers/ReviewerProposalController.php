<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitReviewRequest;
use App\Models\Proposal;
use App\Models\ProposalReviewer;
use App\Models\ProposalReviewScore;
use App\Models\ReviewCriterion;
use App\Models\ReviewDecision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReviewerProposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('super_admin', 'research_admin')) {
            $assignments = ProposalReviewer::with(['proposal.status', 'proposal.type', 'reviewer'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $transformed = $assignments->getCollection()->map(function ($pivot) {
                // Ensure proposal is returned but disguised as having the pivot explicitly named reviewPivot
                $proposal = $pivot->proposal;
                $proposal->reviewPivot = clone $pivot;
                // Avoid recursive loops
                unset($proposal->reviewPivot->proposal); 
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
        $isReviewer = $proposal->reviewers()->where('reviewer_id', $request->user()->id)->exists();

        if (! $isReviewer) {
            abort(403, 'You are not assigned as a reviewer for this proposal.');
        }

        // Load only necessary relations and hide submitter for blind review
        $proposal->load('status', 'type', 'file');
        $proposal->setRelation('submittedBy', null);
        $proposal->setRelation('investigators', collect([]));
        $proposal->submitted_by = null;

        return response()->json($proposal);
    }

    public function storeReview(SubmitReviewRequest $request, Proposal $proposal): JsonResponse
    {
        $reviewerId = $request->user()->id;
        $reviewer = $proposal->reviewers()->where('reviewer_id', $reviewerId)->first();

        if (!$reviewer) {
            abort(403, 'You are not assigned as a reviewer for this proposal.');
        }

        $pivot = ProposalReviewer::where('proposal_id', $proposal->id)
    ->where('reviewer_id', $reviewerId)
    ->first();

if (!$pivot) {
    abort(403, 'Reviewer assignment not found.');
}

        // Save scores per criterion
        foreach ($request->scores as $scoreData) {
            $pivot->scores()->create([
                'criterion_id' => $scoreData['criterion_id'],
                'score' => $scoreData['score'],
                'comments' => $scoreData['comments'] ?? null,
            ]);
        }

        // Update pivot with overall review
        $proposal->reviewers()->updateExistingPivot($reviewerId, [
            'overall_score' => $request->overall_score,
            'overall_comments' => $request->overall_comments,
            'decision_id' => $request->decision_id,
            'submitted_at' => now(),
        ]);

        return response()->json(['message' => 'Review submitted.']);
    }

    public function downloadTemplate(Proposal $proposal, Request $request): StreamedResponse|JsonResponse
    {
        $this->validateReviewer($proposal, $request->user()->id);

        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Review Template');

            // Headers
            $sheet->setCellValue('A1', 'Criterion ID');
            $sheet->setCellValue('B1', 'Criterion Name');
            $sheet->setCellValue('C1', 'Max Score');
            $sheet->setCellValue('D1', 'Score (Your Input)');
            $sheet->setCellValue('E1', 'Comments');

            // Metadata (Hidden or at the end)
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

            // Decision section
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Overall Decision');
            $sheet->setCellValue('B' . $row, 'Your Decision ID Input ->');
            // C will be the input
            $sheet->setCellValue('D' . $row, 'Overall Comments ->');
            // E will be the input for comments
            
            $decisionRow = $row; // Store this for later if needed
            
            $row += 2;
            $sheet->setCellValue('A' . $row, '--- Available Decisions (Do not edit below) ---');
            $row++;
            
            $decisions = \App\Models\ReviewDecision::all();
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

            Log::channel('reviewer')->info('Template downloaded', [
                'proposal_id' => $proposal->id,
                'user_id' => $request->user()->id,
            ]);

            return $response;
        } catch (\Throwable $e) {
            Log::channel('reviewer')->error('Template download failed', [
                'proposal_id' => $proposal->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to generate review template.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ], 500);
        }
    }

    public function importReview(Request $request, Proposal $proposal): JsonResponse
    {
        $this->validateReviewer($proposal, $request->user()->id);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            // Validate Metadata
            $excelProposalId = $sheet->getCell('G2')->getValue();
            $excelReviewerId = $sheet->getCell('H2')->getValue();

            if ($excelProposalId != $proposal->id || $excelReviewerId != $request->user()->id) {
                return response()->json(['message' => 'The uploaded file does not match this proposal assignment.'], 422);
            }

            $pivot = ProposalReviewer::where('proposal_id', $proposal->id)
                ->where('reviewer_id', $request->user()->id)
                ->firstOrFail();

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
                        'criterion_id' => $criterionId,
                        'score' => $receivedScore,
                        'comments' => $comments
                    ];
                }
                $row++;
            }

            // Search for decision after the criteria block
            $decisionId = null;
            $overallComments = '';

            for ($i = $row; $i < $row + 20; $i++) {
                if ($sheet->getCell('A' . $i)->getValue() == 'Overall Decision') {
                    // Based on the new layout, Decision ID is in C, Comments in E
                    $decisionId = $sheet->getCell('C' . $i)->getValue();
                    $overallComments = $sheet->getCell('E' . $i)->getValue();
                    break;
                }
            }

            if (!$decisionId) {
                return response()->json(['message' => 'Overall Decision ID not found in Excel.'], 422);
            }

            $overallScore = $totalMax > 0 ? ($totalReceived / $totalMax) * 5 : 0;

            foreach ($scoresData as $sd) {
                ProposalReviewScore::updateOrCreate(
                    ['proposal_reviewer_id' => $pivot->id, 'criterion_id' => $sd['criterion_id']],
                    ['score' => $sd['score'], 'comments' => $sd['comments']]
                );
            }

            $proposal->reviewers()->updateExistingPivot($request->user()->id, [
                'overall_score' => $overallScore,
                'overall_comments' => $overallComments,
                'decision_id' => $decisionId,
                'submitted_at' => now(),
            ]);

            Log::channel('reviewer')->info('Review imported via Excel', [
                'proposal_id' => $proposal->id,
                'user_id' => $request->user()->id,
                'overall_score' => $overallScore,
                'decision_id' => $decisionId,
            ]);

            return response()->json(['message' => 'Excel review imported successfully.', 'overall_score' => $overallScore]);
        } catch (\Throwable $e) {
            Log::channel('reviewer')->error('Review import failed', [
                'proposal_id' => $proposal->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to import review file.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ], 500);
        }
    }

    private function validateReviewer(Proposal $proposal, int $userId): void
    {
        $isReviewer = $proposal->reviewers()->where('reviewer_id', $userId)->exists();
        if (!$isReviewer) {
            abort(403, 'You are not assigned as a reviewer for this proposal.');
        }
    }
}
