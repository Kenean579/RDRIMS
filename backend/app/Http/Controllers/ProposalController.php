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

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Proposal::class);

        $user = $request->user();
        $proposals = Proposal::with('status', 'type', 'submittedBy.profileImage', 'call', 'financeChecks.status', 'ethicsRequests.approvalStatus', 'file', 'ethicsFile')
            ->hierarchical($user, 'submitted_by')
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                $query->manageableBy($user);
            })
            ->when($request->filled('status'), fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->input('status'))))
            ->when($request->filled('type'), fn($q) => $q->whereHas('type', fn($t) => $t->where('name', $request->input('type'))))
            ->when($request->filled('call_id'), fn($q) => $q->where('call_id', $request->input('call_id')))
            ->when($request->filled('university_id'), fn($q) => $q->where('university_id', $request->input('university_id')))
            ->when($request->filled('campus_id'), fn($q) => $q->where('campus_id', $request->input('campus_id')))
            ->when($request->filled('faculty_id'), fn($q) => $q->where('faculty_id', $request->input('faculty_id')))
            ->when($request->filled('department_id'), fn($q) => $q->where('department_id', $request->input('department_id')))
            ->when($request->filled('search'), fn($q) => $q->where('title', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('keywords', 'LIKE', '%' . $request->input('search') . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($proposals);
    }

    public function store(StoreProposalRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $proposal = Proposal::create([
                ...$request->safe()->except(['investigators', 'proposal_file']),
                'submitted_by' => $request->user()->id,
                'submitted_at' => now(),
                'status_id' => ProposalStatus::where('name', 'draft')->first()->id ?? 1,
            ]);

            // Handle file upload
            if ($request->hasFile('proposal_file')) {
                $file = $this->fileService->upload($request->file('proposal_file'), $request->user()->id, false);
                $proposal->update(['file_id' => $file->id]);
            }

            // Attach investigators
            $investigators = $request->investigators ?? [];
            foreach ($investigators as $investigator) {
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

            return response()->json($proposal->load('investigators', 'file'), 201);
        });
    }

    public function show(Proposal $proposal): JsonResponse
    {
        $user = request()->user();
        
        // Check if user has permission to view this proposal
        $this->authorize('view', $proposal);
        
        // Additional hierarchical check for management rights
        if (!$user->hasRole('super_admin') && !$proposal->isManageableBy($user)) {
            abort(403, 'You do not have permission to view this proposal.');
        }
        
        return response()->json($proposal->load(
            'status', 'type', 'submittedBy.department', 'approvedBy', 'call',
            'reviewers.profileImage',
            'financeChecks', 'ethicsRequests', 'file', 'ethicsFile',
            'investigators.user.profileImage', 'investigators.role', 'academicYear'
        ));
    }

    public function update(UpdateProposalRequest $request, Proposal $proposal): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has permission to update this proposal
        $this->authorize('update', $proposal);
        
        // Additional hierarchical check for management rights
        if (!$user->hasRole('super_admin') && !$proposal->isManageableBy($user)) {
            abort(403, 'You do not have permission to update this proposal.');
        }

        return DB::transaction(function () use ($request, $proposal) {
            $validated = $request->validated();
            $investigators = $validated['investigators'] ?? null;
            unset($validated['investigators']);

            $proposal->update($validated);

            // Handle file upload if provided
            if ($request->hasFile('proposal_file')) {
                $file = $this->fileService->upload($request->file('proposal_file'), $request->user()->id, false);
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

    public function destroy(Proposal $proposal): JsonResponse
    {
        $user = request()->user();
        
        // Check if user has permission to delete this proposal
        $this->authorize('delete', $proposal);
        
        // Additional hierarchical check for management rights
        if (!$user->hasRole('super_admin') && !$proposal->isManageableBy($user)) {
            abort(403, 'You do not have permission to delete this proposal.');
        }
        
        $proposal->delete();
        return response()->json(['message' => 'Proposal deleted.']);
    }

    public function submit(SubmitProposalRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('submit', $proposal);
        $this->proposalService->submit($proposal, $request->user());
        return response()->json(['message' => 'Proposal submitted successfully.', 'proposal' => $proposal]);
    }

    public function approve(Proposal $proposal, Request $request): JsonResponse
    {
        $this->authorize('update', $proposal); // This should ideally be a more specific permission
        $this->proposalService->approve($proposal, $request->user());
        return response()->json(['message' => 'Proposal approved. Project created.']);
    }

    public function reject(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $request->validate(['comment' => 'required|string']);
        $this->proposalService->reject($proposal, $request->user(), $request->input('comment'));
        return response()->json(['message' => 'Proposal rejected.']);
    }

    public function assignReviewers(AssignReviewersRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('assignReviewers', Proposal::class);
        $this->proposalService->assignReviewers($proposal, $request->input('reviewer_ids'), $request->user());
        return response()->json(['message' => 'Reviewers assigned.']);
    }

    public function suggestReviewers(Proposal $proposal): JsonResponse
    {
        $this->authorize('assignReviewers', Proposal::class);
        $suggestions = $this->reviewerSuggestionService->suggest($proposal);
        return response()->json($suggestions);
    }

    public function uploadDocument(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $request->validate(['document' => 'required|file|mimes:pdf,docx,doc|max:10240']);

        $file = $this->fileService->upload($request->file('document'), $request->user()->id);
        
        $proposal->update(['file_id' => $file->id]);

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'file' => $file
        ]);
    }
}
