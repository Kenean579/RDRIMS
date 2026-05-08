<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutputRequest;
use App\Http\Requests\UpdateOutputRequest;
use App\Http\Requests\ChangeOutputStatusRequest;
use App\Models\Output;
use App\Services\OutputService;
use Illuminate\Http\JsonResponse;

class OutputController extends Controller
{
    public function __construct(private OutputService $outputService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Output::class);

        $outputs = Output::with(['category', 'status', 'participants'])
            ->when(!auth()->user()->roles()->where('name', 'admin')->exists(), function ($q) {
                $q->whereHas('participants', fn($p) => $p->where('user_id', auth()->id()));
            })
            ->latest()
            ->paginate(20);

        return response()->json($outputs);
    }

    public function store(StoreOutputRequest $request): JsonResponse
    {
        $output = Output::create($request->validated());
        return response()->json($output, 201);
    }

    public function show(Output $output): JsonResponse
    {
        $this->authorize('view', $output);
        $output->load(['category', 'status', 'participants.user', 'files']);
        return response()->json($output);
    }

    public function update(UpdateOutputRequest $request, Output $output): JsonResponse
    {
        $output->update($request->validated());
        return response()->json($output);
    }

    public function destroy(Output $output): JsonResponse
    {
        $this->authorize('delete', $output);
        $output->delete();
        return response()->json(null, 204);
    }

    public function changeStatus(ChangeOutputStatusRequest $request, Output $output): JsonResponse
    {
        $newStatus = $request->input('status');
        $this->outputService->changeStatus($output, $newStatus);
        return response()->json($output->fresh());
    }
}