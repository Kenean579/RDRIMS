<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveFundingRequest;
use App\Http\Requests\StoreFundingRequest;
use App\Http\Requests\UpdateFundingRequest;
use App\Http\Resources\FundingResource;
use App\Models\Funding;
use App\Services\FundingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FundingController extends Controller
{
    public function __construct(
        private FundingService $fundingService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userUniversityId = $request->user()->university_id ?? $request->user()->getUniversityId();
        
        $query = Funding::with('status', 'fundingSource', 'createdBy')
            ->forUniversity($userUniversityId)
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return FundingResource::collection($query)->response();
    }

    public function store(StoreFundingRequest $request): JsonResponse
    {
        $this->authorize('create', Funding::class);

        $data = $request->validated();
        $data['university_id'] = $request->user()->university_id ?? $request->user()->getUniversityId();

        $funding = $this->fundingService->create($data, $request->user()->id);

        return response()->json(new FundingResource($funding), 201);
    }

    public function show(Funding $funding): JsonResponse
    {
        $this->authorize('view', $funding);

        $funding->load([
            'status',
            'fundingSource',
            'project',
            'proposal',
            'createdBy',
            'approvedBy',
            'allocations.budgetCategory',
            'expenses.budgetCategory',
            'expenses.expenseCategory',
            'expenses.submittedBy',
            'expenses.approvedBy',
            'approvals.approvedBy',
        ]);

        return response()->json(new FundingResource($funding));
    }

    public function update(UpdateFundingRequest $request, Funding $funding): JsonResponse
    {
        $this->authorize('update', $funding);

        $funding = $this->fundingService->update($funding, $request->validated(), $request->user()->id);

        return response()->json(new FundingResource($funding));
    }

    public function destroy(Funding $funding): JsonResponse
    {
        $this->authorize('delete', $funding);

        $funding->delete();

        return response()->json(['message' => 'Funding deleted successfully.']);
    }

    public function submit(Request $request, Funding $funding): JsonResponse
    {
        $this->authorize('submit', $funding);

        $funding = $this->fundingService->submit($funding, $request->user()->id);

        return response()->json(new FundingResource($funding));
    }

    public function approve(ApproveFundingRequest $request, Funding $funding): JsonResponse
    {
        $this->authorize('approve', $funding);

        $funding = $this->fundingService->approve(
            $funding,
            $request->user()->id,
            $request->comments
        );

        return response()->json(new FundingResource($funding));
    }

    public function reject(ApproveFundingRequest $request, Funding $funding): JsonResponse
    {
        $this->authorize('reject', $funding);

        $funding = $this->fundingService->reject(
            $funding,
            $request->user()->id,
            $request->comments
        );

        return response()->json(new FundingResource($funding));
    }

    public function budgetStats(Funding $funding): JsonResponse
    {
        $this->authorize('view', $funding);

        $stats = $this->fundingService->getBudgetStats($funding);

        return response()->json($stats);
    }
}
