<?php
// app/Http/Controllers/Api/ProposalController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProposalRequest;
use App\Http\Requests\UpdateProposalRequest;
use App\Http\Resources\DetectionRequestResource;
use App\Http\Resources\EthicsRequestResource;
use App\Http\Resources\FinanceCheckResource;
use App\Http\Resources\ProposalResource;
use App\Models\Call;
use App\Models\DetectionRequest;
use App\Models\DetectionService;
use App\Models\DetectionStatus;
use App\Models\EthicsApprovalStatus;
use App\Models\File;
use App\Models\FinanceCheckStatus;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\ProposalType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;
use Symfony\Component\HttpFoundation\Response;

class ProposalController extends Controller
{
    // List proposals (with dynamic filters)
    public function index(Request $request)
    {
        $query = Proposal::with(['call', 'type', 'status', 'submittedBy', 'academicYear']);

        // Filter by call
        if ($request->has('call_id')) {
            $query->where('call_id', $request->call_id);
        }

        // Filter by status name (dynamic)
        if ($request->has('status_name')) {
            $statusId = ProposalStatus::where('name', $request->status_name)->value('id');
            if ($statusId) $query->where('status_id', $statusId);
        }

        // Researchers see only their own proposals
        $user = $request->user();
        if (!$user->hasRole('admin') && !$user->hasRole('research_admin')) {
            $query->where('submitted_by', $user->id);
        }

        $proposals = $query->latest()->paginate($request->get('per_page', 15));
        return ProposalResource::collection($proposals);
    }

    // Store a new proposal (draft)
    public function store(StoreProposalRequest $request)
    {
        DB::beginTransaction();
        try {
            // Resolve type_id by name (dynamic)
            $typeId = ProposalType::where('name', $request->type_name)->firstOrFail()->id;
            $draftStatusId = ProposalStatus::where('name', 'draft')->firstOrFail()->id;

            $proposal = Proposal::create([
                'call_id'           => $request->call_id,
                'type_id'           => $typeId,
                'title'             => $request->title,
                'abstract'          => $request->abstract,
                'objectives'        => $request->objectives,
                'methodology'       => $request->methodology,
                'keywords'          => $request->keywords,
                'budget'            => $request->budget,
                'budget_allocation' => $request->budget_allocation,
                'status_id'         => $draftStatusId,
                'submitted_by'      => auth()->id(),
                'academic_year_id'  => $request->academic_year_id,
            ]);

            // Upload proposal file if provided
            if ($request->hasFile('proposal_file')) {
                $file = app(FileController::class)->storeFile($request->file('proposal_file'), auth()->id());
                $proposal->update(['file_id' => $file->id]);
            }

            DB::commit();
            return new ProposalResource($proposal->load(['type', 'status', 'submittedBy']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Submit a draft proposal (change status to 'submitted')
    public function submit(Proposal $proposal)
    {
        $user = auth()->user();
        if ($proposal->submitted_by !== $user->id && !$user->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $submittedId = ProposalStatus::where('name', 'submitted')->value('id');
        $proposal->update([
            'status_id' => $submittedId,
            'submitted_at' => now(),
            'status_change_comment' => null,
        ]);

        return new ProposalResource($proposal);
    }

    // Assign reviewers (dynamic – get reviewer role ID from role name)
    public function assignReviewers(Request $request, Proposal $proposal)
    {
        // Only allowed if proposal status is 'submitted' or 'under_review'
        $allowedStatuses = ['submitted', 'under_review'];
        $currentStatusName = $proposal->status->name;
        if (!in_array($currentStatusName, $allowedStatuses)) {
            return response()->json(['success' => false, 'message' => 'Cannot assign reviewers at this stage'], 422);
        }

        $validated = $request->validate([
            'reviewer_ids' => 'required|array|min:1',
            'reviewer_ids.*' => 'exists:users,id',
        ]);

        $underReviewId = ProposalStatus::where('name', 'under_review')->value('id');
        $proposal->status_id = $underReviewId;

        foreach ($validated['reviewer_ids'] as $reviewerId) {
            $proposal->reviewers()->attach($reviewerId, [
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]);
        }
        $proposal->save();

        return response()->json(['success' => true, 'message' => 'Reviewers assigned']);
    }

    // Approve proposal (only after finance check and reviews)
    public function approve(Proposal $proposal)
    {
        // Check that finance check is approved
        $latestFinance = $proposal->financeChecks()->latest()->first();
        if (!$latestFinance || $latestFinance->status->name !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Finance check not approved'], 422);
        }

        // Check that at least two reviews are submitted (example rule)
        $submittedReviews = $proposal->reviewers()->wherePivotNotNull('submitted_at')->count();
        if ($submittedReviews < 2) {
            return response()->json(['success' => false, 'message' => 'Need at least 2 completed reviews'], 422);
        }

        $approvedId = ProposalStatus::where('name', 'approved')->value('id');
        $proposal->update([
            'status_id' => $approvedId,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // TODO: Notify Nunu (Developer C) to create a project
        return new ProposalResource($proposal);
    }




    // === Reviewer Suggestion Endpoint ===
public function suggestReviewers(Proposal $proposal, ReviewerSuggestionService $suggester)
{
    $suggested = $suggester->suggest($proposal, 5);
    return response()->json([
        'success' => true,
        'data' => $suggested->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'expertise' => $user->expertise->pluck('name'),
        ]),
    ]);
}

// === Finance Check ===
public function storeFinanceCheck(FinanceCheckRequest $request, Proposal $proposal)
{
    // Only finance officer or admin can perform finance check
    $user = auth()->user();
    if (!$user->hasRole('finance_officer') && !$user->hasRole('admin')) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $statusId = FinanceCheckStatus::where('name', $request->status_name)->value('id');

    $financeCheck = $proposal->financeChecks()->create([
        'checker_id' => $user->id,
        'status_id'  => $statusId,
        'comments'   => $request->comments,
        'checked_at' => now(),
    ]);

    // If finance check is approved, you might automatically move proposal status
    if ($request->status_name === 'approved') {
        // Optionally update proposal status to next step
        // $nextStatusId = ProposalStatus::where('name', 'under_review')->value('id');
        // $proposal->update(['status_id' => $nextStatusId]);
    }

    return new FinanceCheckResource($financeCheck);
}

// === Ethics Request ===
public function storeEthicsRequest(EthicsRequestForm $request, Proposal $proposal)
{
    // Generate PDF from proposal data (using DomPDF)
    $pdf = \PDF::loadView('pdf.ethics-request', ['proposal' => $proposal]);
    $path = 'ethics/' . uniqid() . '.pdf';
    Storage::disk('public')->put($path, $pdf->output());

    $ethicsRequest = $proposal->ethicsRequests()->create([
        'generated_pdf_path' => $path,
        'submitted_to_irb'   => $request->submitted_to_irb ?? false,
        'approval_status_id' => EthicsApprovalStatus::where('name', 'pending')->value('id'),
        'comments'           => $request->comments,
        'version'            => ($proposal->ethicsRequests()->max('version') ?? 0) + 1,
    ]);

    // Optionally update proposal's ethics approval status
    $pendingId = EthicsApprovalStatus::where('name', 'pending')->value('id');
    $proposal->update([
        'ethics_approval_status_id' => $pendingId,
        'ethics_file_id' => null, // you can attach a file if uploaded
    ]);

    return new EthicsRequestResource($ethicsRequest);
}

// === Plagiarism/AI Detection Request ===
public function requestDetection(Request $request, Proposal $proposal)
{
    $validated = $request->validate([
        'service_name' => 'required|string|exists:detection_services,name',
        'file_id'      => 'required|exists:files,id',
    ]);

    $serviceId = DetectionService::where('name', $validated['service_name'])->value('id');
    $pendingId = DetectionStatus::where('name', 'pending')->value('id');

    $detectionRequest = DetectionRequest::create([
        'detectable_type' => 'proposal',
        'detectable_id'   => $proposal->id,
        'file_id'         => $validated['file_id'],
        'service_id'      => $serviceId,
        'status_id'       => $pendingId,
        'requested_by'    => auth()->id(),
        'requested_at'    => now(),
    ]);

    // Dispatch background job to process detection (optional)
    // ProcessDetection::dispatch($detectionRequest);

    return new DetectionRequestResource($detectionRequest);
}

// === Get detection result ===
public function getDetectionResult(Proposal $proposal, DetectionRequest $detectionRequest)
{
    if ($detectionRequest->detectable_id !== $proposal->id) {
        return response()->json(['success' => false, 'message' => 'Mismatch'], 422);
    }
    return new DetectionRequestResource($detectionRequest->load('result'));
}
}
