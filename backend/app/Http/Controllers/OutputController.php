<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeOutputStatusRequest;
use App\Http\Requests\StoreOutputRequest;
use App\Http\Requests\UpdateOutputRequest;
use App\Models\Output;
use App\Services\OutputService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutputController extends Controller
{
    public function __construct(
        private OutputService $outputService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $outputs = Output::with('category', 'status', 'subtype')
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->category, fn($q) => $q->whereHas('category', fn($c) => $c->where('name', $request->category)))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($outputs);
    }

    public function store(StoreOutputRequest $request): JsonResponse
    {
        $output = Output::create(['status_id' => 1, ...$request->validated()]); // draft
        return response()->json($output, 201);
    }

    public function show(Output $output): JsonResponse
    {
        return response()->json($output->load('category', 'status', 'participants.user', 'files', 'project'));
    }

    public function update(UpdateOutputRequest $request, Output $output): JsonResponse
    {
        $this->authorize('update', $output);
        $output->update($request->validated());
        return response()->json($output);
    }

    public function destroy(Output $output): JsonResponse
    {
        $this->authorize('delete', $output);
        $output->delete();
        return response()->json(['message' => 'Output deleted.']);
    }

    public function changeStatus(ChangeOutputStatusRequest $request, Output $output): JsonResponse
    {
        $this->outputService->changeStatus($output, $request->status_id, $request->user());
        return response()->json(['message' => 'Status updated.']);
    }
}