<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEthicsRequestRequest;
use App\Http\Requests\UpdateEthicsRequestRequest;
use App\Models\EthicsRequest;
use App\Models\Proposal;
use App\Services\EthicsService;
use Illuminate\Http\JsonResponse;

class EthicsRequestController extends Controller
{
    public function __construct(
        private EthicsService $ethicsService,
    ) {}

    public function store(StoreEthicsRequestRequest $request, Proposal $proposal): JsonResponse
    {
        $ethicsRequest = $this->ethicsService->submitRequest($proposal, $request->validated(), $request->user());
        return response()->json($ethicsRequest, 201);
    }

    public function update(UpdateEthicsRequestRequest $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('update', $ethicsRequest);
        $ethicsRequest->update($request->validated());
        return response()->json($ethicsRequest);
    }

    public function index(): JsonResponse
    {
        return response()->json(EthicsRequest::with('proposal')->get());
    }

    public function show(EthicsRequest $ethicsRequest): JsonResponse
    {
        return response()->json($ethicsRequest->load('proposal'));
    }

    public function decision(\Illuminate\Http\Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:approved,rejected,needs_revision',
            'decision_note' => 'nullable|string'
        ]);
        
        $ethicsRequest->update([
            'status' => $validated['status'],
            'decision_note' => $validated['decision_note'] ?? $ethicsRequest->decision_note
        ]);

        return response()->json($ethicsRequest);
    }
}
