<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignReviewersRequest;
use App\Http\Requests\StoreProposalRequest;
use App\Http\Requests\SubmitProposalRequest;
use App\Http\Requests\UpdateProposalRequest;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Services\ProposalService;
use App\Services\ReviewerSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    public function __construct(
        private ProposalService $proposalService,
        private ReviewerSuggestionService $reviewerSuggestionService,
        private \App\Services\FileService $fileService,
    ) {}

    /**
     * List proposals – scoped automatically by the hierarchical trait.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Proposal::class);

        $user = $request->user();

        $proposals = Proposal::with([
                'status', 'type', 'submittedBy.profileImage', 'call',
                'financeChecks.status', 'ethicsRequests.approvalStatus',
                'file', 'ethicsFile'
            ])
            // The hierarchical scope already handles:
            //   super_admin    → empty result (platform only)
            //   research_admin → university
            //   campus_admin   → campus
            //   faculty_admin  → faculty
            //   department_head→ department
            //   director       → research centre
            //   researcher     → own proposals
            //   reviewer       → own proposals (if also researcher)
            ->hierarchical($user, 'submitted_by')

            // Filters
            ->when($request->filled('status'), fn($q) =>
                $q->whereHas('status', fn($s) => $s->where('name', $request->input('status')))
            )
            ->when($request->filled('type'), fn($q) =>
                $q->whereHas('type', fn($t) => $t->where('name', $request->input('type')))
            )
            ->when($request->filled('call_id'), fn($q) =>
                $q->where('call_id', $request->input('call_id'))
            )
            ->when($request->filled('search'), fn($q) =>
                $q->where('title', 'LIKE', '%' . $request->input('search') . '%')
                  ->orWhere('keywords', 'LIKE', '%' . $request->input('search') . '%')
            )
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($proposals);
    }

    /**
     * Store a new proposal (draft).
     */
    public function store(StoreProposalRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $proposal = Proposal::create([
                ...$request->safe()->except(['investigators', 'proposal_file']),
                'submitted_by' => $request->user()->id,
                'submitted_at'  => now(),
                'status_id'     => ProposalStatus::where('name', 'draft')->first()->id ?? 1,
                // Auto‑set research centre from the user's primary centre
                'research_center_id' => $request->user()->research_center_id,
            ]);

            // Handle file upload
            if ($request->hasFile('proposal_file')) {
                $file = $this->fileService->upload(
                    $request->file('proposal_file'), $request->user()->id, false
                );
                $proposal->update(['file_id' => $file->id]);
            }

            // Attach investigators
            $investigators = $request->investigators ?? [];
            foreach ($investigators as $investigator) {
                $proposal->investigators()->create([
                    'user_id'     => $investigator['user_id'] ?? null,
                    'name'        => $investigator['name'] ?? null,
                    'email'       => $investigator['email'] ?? null,
                    'institution' => $investigator['institution'] ?? null,
                    'role_id'     => $investigator['role_id'],
                    'status_id'   => 1, // pending
                    'invited_at'  => now(),
                ]);
            }

            return response()->json($proposal->load('investigators', 'file'), 201);
        });
    }

    /**
     * Show a single proposal (policy + hierarchical check already applied).
     */
    public function show(Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);

        return response()->json($proposal->load(
            'status', 'type', 'submittedBy.department', 'approvedBy', 'call',
            'reviewers.profileImage',
            'financeChecks', 'ethicsRequests', 'file', 'ethicsFile',
            'investigators.user.profileImage', 'investigators.role', 'academicYear'
        ));
    }

    /**
     * Update a proposal (draft only).
     */
    public function update(UpdateProposalRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);

        return DB::transaction(function () use ($request, $proposal) {
            $validated = $request->validated();
            $investigators = $validated['investigators'] ?? null;
            unset($validated['investigators']);

            $proposal->update($validated);

            // Handle file upload if provided
            if ($request->hasFile('proposal_file')) {
                $file = $this->fileService->upload(
                    $request->file('proposal_file'), $request->user()->id, false
                );
                $proposal->update(['file_id' => $file->id]);
            }

            // Replace investigators if provided
            if ($investigators !== null) {
                $proposal->investigators()->delete();
                foreach ($investigators as $inv) {
                    $proposal->investigators()->create([
                        'user_id'     => $inv['user_id'] ?: null,
                        'name'        => $inv['name'] ?? null,
                        'email'       => $inv['email'] ?? null,
                        'institution' => $inv['institution'] ?? null,
                        'role_id'     => $inv['role_id'],
                        'status_id'   => 1,
                        'invited_at'  => now(),
                    ]);
                }
            }

            return response()->json($proposal->load('investigators', 'file', 'status', 'type'));
        });
    }

    /**
     * Delete a proposal (draft only).
     */
    public function destroy(Proposal $proposal): JsonResponse
    {
        $this->authorize('delete', $proposal);
        $proposal->delete();
        return response()->json(['message' => 'Proposal deleted.']);
    }

    /**
     * Submit a draft proposal.
     */
    public function submit(SubmitProposalRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('submit', $proposal);
        $this->proposalService->submit($proposal, $request->user());
        return response()->json(['message' => 'Proposal submitted successfully.', 'proposal' => $proposal]);
    }

    /**
     * Approve a proposal (admin).
     */
    public function approve(Proposal $proposal, Request $request): JsonResponse
    {
        $this->authorize('update', $proposal);
        $this->proposalService->approve($proposal, $request->user());
        return response()->json(['message' => 'Proposal approved. Project created.']);
    }

    /**
     * Reject a proposal (admin).
     */
    public function reject(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $request->validate(['comment' => 'required|string']);
        $this->proposalService->reject($proposal, $request->user(), $request->input('comment'));
        return response()->json(['message' => 'Proposal rejected.']);
    }

    /**
     * Assign reviewers (admin).
     */
    public function assignReviewers(AssignReviewersRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('assignReviewers', Proposal::class);
        $this->proposalService->assignReviewers(
            $proposal, $request->input('reviewer_ids'), $request->user()
        );
        return response()->json(['message' => 'Reviewers assigned.']);
    }

    /**
     * Auto‑suggest reviewers based on keywords (admin).
     */
    public function suggestReviewers(Proposal $proposal): JsonResponse
    {
        $this->authorize('assignReviewers', Proposal::class);
        $suggestions = $this->reviewerSuggestionService->suggest($proposal);
        return response()->json($suggestions);
    }

    /**
     * Upload a document to an existing proposal.
     */
    public function uploadDocument(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $request->validate(['document' => 'required|file|mimes:pdf,docx,doc|max:10240']);

        $file = $this->fileService->upload($request->file('document'), $request->user()->id);
        $proposal->update(['file_id' => $file->id]);

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'file'    => $file
        ]);
    }
}