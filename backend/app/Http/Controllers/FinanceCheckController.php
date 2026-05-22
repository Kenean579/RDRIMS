<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinanceCheckRequest;
use App\Http\Requests\UpdateFinanceCheckRequest;
use App\Models\FinanceCheck;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceCheckController extends Controller
{
    public function store(StoreFinanceCheckRequest $request, Proposal $proposal): JsonResponse
    {
        $check = $proposal->financeChecks()->create([
            'status_id' => $request->status_id ?? FinanceCheck::getStatusId('pending'),
            'comments' => $request->comments,
            'checker_id' => $request->user()->id,
            'checked_at' => now(),
        ]);

        return response()->json($check, 201);
    }

    public function update(UpdateFinanceCheckRequest $request, FinanceCheck $financeCheck): JsonResponse
    {
        $this->authorize('update', $financeCheck);
        $financeCheck->update($request->validated());
        return response()->json($financeCheck);
    }
}
