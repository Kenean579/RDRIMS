<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignReviewersRequest;
use App\Http\Requests\StoreProposalRequest;
use App\Http\Requests\SubmitProposalRequest;
use App\Http\Requests\UpdateProposalRequest;
use App\Models\Proposal;
use App\Services\ProposalService;
use App\Services\ReviewerSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function __construct(
        private ProposalService $proposalService,
        private ReviewerSuggestionService $reviewerSuggestionService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Proposal::class);

        $proposals = Proposal::with('status', 'type', 'submittedBy', 'call')
            ->when(! $request->user()->isAdmin(), fn($q) => $q->where('submitted_by', $request->user()->id))
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->call_id, fn($q) => $q->where('call_id', $request->call_id))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('keywords', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($proposals);
    }

    public function store(StoreProposalRequest $request): JsonResponse
    {
        $proposal = Proposal::create([
            ...$request->safe()->except('investigators'),
            'submitted_by' => $request->user()->id,
            'submitted_at' => now(),
            'status_id' => Proposal::getStatusId('draft'),
        ]);

        // Attach investigators
        foreach ($request->investigators as $investigator) {
            $proposal->investigators()->create([
                'user_id' => $investigator['user_id'] ?? null,
                'name' => $investigator['name'] ?? null,
                'email' => $investigator['email'] ?? null,
                'institution' => $investigator['institution'] ?? null,
                'role_id' => $investigator['role_id'],
                'status_id' => 1, // pending
                'invited_at' => now(),
            ]);
        }

        return response()->json($proposal->load('investigators'), 201);
    }

    public function show(Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);
        return response()->json($proposal->load(
            'status', 'type', 'submittedBy', 'approvedBy', 'call',
            'reviewers.reviewPivot.scores.criterion',
            'financeChecks', 'ethicsRequests', 'file'
        ));
    }

    public function update(UpdateProposalRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $proposal->update($request->validated());
        return response()->json($proposal);
    }

    public function destroy(Proposal $proposal): JsonResponse
    {
        $this->authorize('delete', $proposal);
        $proposal->delete();
        return response()->json(['message' => 'Proposal deleted.']);
    }

    public function submit(SubmitProposalRequest $request, Proposal $proposal): JsonResponse
    {
        $this->proposalService->submit($proposal, $request->user());
        return response()->json(['message' => 'Proposal submitted successfully.', 'proposal' => $proposal]);
    }

    public function approve(Proposal $proposal, Request $request): JsonResponse
    {
        $this->authorize('update', $proposal);
        $this->proposalService->approve($proposal, $request->user());
        return response()->json(['message' => 'Proposal approved. Project created.']);
    }

    public function reject(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $request->validate(['comment' => 'required|string']);
        $this->proposalService->reject($proposal, $request->user(), $request->comment);
        return response()->json(['message' => 'Proposal rejected.']);
    }

    public function assignReviewers(AssignReviewersRequest $request, Proposal $proposal): JsonResponse
    {
        $this->proposalService->assignReviewers($proposal, $request->reviewer_ids, $request->user());
        return response()->json(['message' => 'Reviewers assigned.']);
    }

    public function suggestReviewers(Proposal $proposal): JsonResponse
    {
        $suggestions = $this->reviewerSuggestionService->suggest($proposal);
        return response()->json($suggestions);
    }
}
