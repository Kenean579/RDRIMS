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
        $ethicsRequest = $this->ethicsService->generatePdf($proposal);
        return response()->json($ethicsRequest, 201);
    }

    public function update(UpdateEthicsRequestRequest $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('update', $ethicsRequest);
        $ethicsRequest->update($request->validated());
        return response()->json($ethicsRequest);
    }
}
