<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddInvestigatorRequest;
use App\Http\Requests\ChangeProjectStatusRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Proposal;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Project::with('status', 'pi.profileImage', 'academicYear', 'coverImage')
            ->hierarchical($request->user(), 'pi_id')
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%'))
            ->when($request->overdue, fn($q) => $q->overdue())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(ProjectResource::collection($query));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);
        
        $project = $this->projectService->create(
            $request->validated(),
            $request->user()->id
        );
        
        return response()->json(new ProjectResource($project), 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        
        $project->load([
            'status',
            'pi.profileImage',
            'investigators.user',
            'milestones.tasks.status',
            'milestones.status',
            'expenses.approvedBy',
            'publications',
            'patents',
            'outputs',
            'coverImage',
            'createdBy',
            'updatedBy',
        ]);
        
        return response()->json(new ProjectResource($project));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        
        $project = $this->projectService->update(
            $project,
            $request->validated(),
            $request->user()->id
        );
        
        return response()->json(new ProjectResource($project));
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        
        return response()->json(['message' => 'Project deleted successfully.']);
    }

    /**
     * Submit project for approval
     */
    public function submit(Project $project): JsonResponse
    {
        $this->authorize('submit', $project);
        
        try {
            $project = $this->projectService->submit($project, request()->user()->id);
            return response()->json(new ProjectResource($project));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Approve a project
     */
    public function approve(Request $request, Project $project): JsonResponse
    {
        $this->authorize('approve', $project);
        
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);
        
        try {
            $project = $this->projectService->approve(
                $project,
                $request->user()->id,
                $request->comments
            );
            return response()->json(new ProjectResource($project));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reject a project
     */
    public function reject(Request $request, Project $project): JsonResponse
    {
        $this->authorize('approve', $project); // Same permission as approve
        
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        try {
            $project = $this->projectService->reject(
                $project,
                $request->user()->id,
                $request->reason
            );
            return response()->json(new ProjectResource($project));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Suspend a project
     */
    public function suspend(Request $request, Project $project): JsonResponse
    {
        $this->authorize('changeStatus', $project);
        
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        try {
            $project = $this->projectService->suspend(
                $project,
                $request->user()->id,
                $request->reason
            );
            return response()->json(new ProjectResource($project));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reactivate a suspended project
     */
    public function reactivate(Project $project): JsonResponse
    {
        $this->authorize('changeStatus', $project);
        
        try {
            $project = $this->projectService->reactivate($project, request()->user()->id);
            return response()->json(new ProjectResource($project));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mark project as completed
     */
    public function complete(Project $project): JsonResponse
    {
        $this->authorize('complete', $project);
        
        try {
            $project = $this->projectService->complete($project, request()->user()->id);
            return response()->json(new ProjectResource($project));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get project progress statistics
     */
    public function progress(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        
        $stats = $this->projectService->calculateProgress($project);
        return response()->json($stats);
    }

    /**
     * Get project budget statistics
     */
    public function budgetStats(Project $project): JsonResponse
    {
        $this->authorize('viewFinancials', $project);
        
        $stats = $this->projectService->getBudgetStats($project);
        return response()->json($stats);
    }

    /**
     * Validate project timeline
     */
    public function timeline(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        
        $validation = $this->projectService->validateTimeline($project);
        return response()->json($validation);
    }

    /**
     * Create project from approved proposal
     */
    public function createFromProposal(Proposal $proposal, Request $request): JsonResponse
    {
        $this->authorize('view', $proposal);
        
        $project = $this->projectService->createFromProposal($proposal, $request->user());
        return response()->json(new ProjectResource($project), 201);
    }

    /**
     * Legacy status change endpoint (deprecated, use specific endpoints)
     * @deprecated Use submit, approve, suspend, reactivate, complete instead
     */
    public function changeStatus(ChangeProjectStatusRequest $request, Project $project): JsonResponse
    {
        $this->authorize('changeStatus', $project);
        
        $status = \App\Models\ProjectStatus::where('name', $request->status)->first();
        
        if (!$status) {
            return response()->json(['message' => 'Invalid status'], 422);
        }
        
        $project->update([
            'status_id' => $status->id,
            'updated_by' => $request->user()->id,
        ]);
        
        return response()->json(new ProjectResource($project->load('status')));
    }

    /**
     * Add investigator to project
     */
    public function addInvestigator(AddInvestigatorRequest $request, Project $project): JsonResponse
    {
        $this->authorize('manageTeam', $project);
        
        try {
            $investigator = $this->projectService->addInvestigator(
                $project,
                $request->user_id,
                $request->role,
                $request->user()->id
            );
            
            return response()->json([
                'message' => 'Investigator added successfully.',
                'investigator' => $investigator->load('user'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove investigator from project
     */
    public function removeInvestigator(Project $project, int $investigatorId): JsonResponse
    {
        $this->authorize('manageTeam', $project);
        
        try {
            $this->projectService->removeInvestigator($project, $investigatorId, request()->user()->id);
            return response()->json(['message' => 'Investigator removed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}