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
            ->where(function ($query) use ($user) {
                // Super Admin sees everything
                if ($user->hasRole('super_admin')) {
                    return;
                }

                if ($user->isAdmin()) {
                    // Admins see proposals within their hierarchy or submitted to calls belonging to their university
                    $query->hierarchical($user, 'submitted_by')
                          ->orWhereHas('call', function ($cq) use ($user) {
                              $cq->where('university_id', $user->resolvedUniversityId());
                          });
                } else {
                    // Researchers, reviewers, students can only see proposals they submitted or where they are investigators/reviewers
                    $query->where('submitted_by', $user->id)
                          ->orWhereIn('id', function ($sub) use ($user) {
                              $sub->select('proposal_id')->from('proposal_investigators')
                                  ->where('user_id', $user->id);
                          });

                    if ($user->hasRole('reviewer')) {
                        $query->orWhereIn('id', function ($sub) use ($user) {
                            $sub->select('proposal_id')->from('proposal_reviewers')
                                ->where('reviewer_id', $user->id);
                        });
                    }
                }
            })

            // Filters
            ->when($request->filled('status'), function($q) use ($request) {
                $status = $request->input('status');
                if (is_array($status)) {
                    $q->whereHas('status', fn($s) => $s->whereIn('name', $status));
                } else {
                    $q->whereHas('status', fn($s) => $s->where('name', $status));
                }
            })
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
            $call = null;
            if ($request->call_id) {
                $call = \App\Models\Call::withoutGlobalScopes()->find($request->call_id);
                if (! $call || ! $request->user()->can('view', $call)) {
                    abort(403, 'You do not have access to this call.');
                }

                if ($call && $call->deadline < now()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'call_id' => 'The deadline for this call has passed.'
                    ]);
                }
            }

            $academicYearId = $request->academic_year_id;
            if (!$academicYearId) {
                $academicYearId = \App\Models\AcademicYear::where('is_current', true)->first()?->id;
            }

            $proposal = Proposal::create([
                ...$request->safe()->except(['investigators', 'proposal_file', 'ethics_file', 'academic_year_id']),
                'submitted_by' => $request->user()->id,
                'submitted_at'  => now(),
                'status_id'     => ProposalStatus::where('name', 'submitted')->first()->id ?? 1,
                'academic_year_id' => $academicYearId,
                // Inherit center from call if available, else use user's primary
                'research_center_id' => $call?->research_center_id ?? $request->user()->research_center_id,
            ]);

            // Handle file upload
            if ($request->hasFile('proposal_file')) {
                $file = $this->fileService->upload(
                    $request->file('proposal_file'), $request->user()->id, false
                );
                $proposal->update(['file_id' => $file->id]);
            }

            // Handle ethics file
            if ($request->hasFile('ethics_file')) {
                $ethicsFile = $this->fileService->upload(
                    $request->file('ethics_file'), $request->user()->id, false
                );
                $proposal->update(['ethics_file_id' => $ethicsFile->id]);
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

            // Log submission
            $auditLogService = app()->make(\App\Services\AuditLogService::class);
            $auditLogService->log('submitted', 'proposals', $proposal->id, $request);

            // Send notifications to submitter
            $notificationService = app()->make(\App\Services\NotificationService::class);
            $notificationService->proposalSubmitted($request->user(), $proposal->title, $proposal->id);

            // Notify Call Creator
            if ($call && $call->createdBy && $call->createdBy->id !== $request->user()->id) {
                $notificationService->proposalReceived($call->createdBy, $proposal->title, $proposal->id, $request->user()->name);
            }

            // Notify Research Admins (Research Directorate Admins) of the university
            $universityId = $call?->university_id ?: $request->user()->university_id;
            if ($universityId) {
                $researchAdmins = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'research_admin'))
                    ->where(function($q) use ($universityId) {
                        $q->where('university_id', $universityId)
                          ->orWhereNull('university_id');
                    })
                    ->get();
                foreach ($researchAdmins as $admin) {
                    if ($admin->id !== $request->user()->id) {
                        $notificationService->proposalReceived($admin, $proposal->title, $proposal->id, $request->user()->name);
                    }
                }
            }

            return response()->json($proposal->load('investigators', 'file'), 201);
        });
    }

    /**
     * Show a single proposal (policy + hierarchical check already applied).
     */
    public function show(Proposal $proposal, Request $request): JsonResponse
    {
        $this->authorize('view', $proposal);

        $user = $request->user();
        $proposal->load([
            'status', 'type', 'submittedBy.department', 'approvedBy', 'call',
            'reviewers.profileImage',
            'financeChecks', 'ethicsRequests', 'file', 'ethicsFile',
            'investigators.user.profileImage', 'investigators.role', 'academicYear'
        ]);

        // ENFORCE BLIND REVIEW
        // If the user is a reviewer for this proposal, and NOT an admin or the owner
        $isReviewer = $proposal->reviewers()->where('reviewer_id', $user->id)->exists();
        $isAdmin = $user->isAdmin();
        $isOwner = $proposal->submitted_by === $user->id;

        if ($isReviewer && !$isAdmin && !$isOwner) {
            $proposal->setRelation('submittedBy', null);
            $proposal->setRelation('investigators', collect([]));
            $proposal->submitted_by = null;
        }

        $proposal->review_progress = app(\App\Services\ReviewService::class)
            ->getProposalReviewProgress($proposal);

        return response()->json($proposal);
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
            unset($validated['proposal_file']);
            unset($validated['ethics_file']);

            $proposal->update($validated);

            // Handle file upload if provided
            if ($request->hasFile('proposal_file')) {
                $file = $this->fileService->upload(
                    $request->file('proposal_file'), $request->user()->id, false
                );
                $proposal->update(['file_id' => $file->id]);
            }

            if ($request->hasFile('ethics_file')) {
                $ethicsFile = $this->fileService->upload(
                    $request->file('ethics_file'), $request->user()->id, false
                );
                $proposal->update(['ethics_file_id' => $ethicsFile->id]);
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
     * Run automated institutional checks (admin).
     */
    public function runChecks(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('update', $proposal);
        $this->proposalService->runChecks($proposal, $request->user());
        return response()->json(['message' => 'Background checks initiated.', 'proposal' => $proposal->fresh('status')]);
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
        $this->authorize('assignReviewers', $proposal);
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
        $this->authorize('assignReviewers', $proposal);
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
