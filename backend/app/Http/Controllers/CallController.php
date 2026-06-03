<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Models\Call;
use Illuminate\Http\JsonResponse;

class CallController extends Controller
{
    public function index(): JsonResponse
    {
        $calls = Call::with('status', 'academicYear', 'createdBy.profileImage', 'guidelineFile')
            ->when(request('status'), fn($q) => $q->whereHas('status', fn($s) => $s->where('name', request('status'))))
            ->orderBy('deadline', 'desc')
            ->paginate(20);

        return response()->json($calls);
    }

    public function store(StoreCallRequest $request): JsonResponse
    {
        $call = Call::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json($call, 201);
    }

    public function show(Call $call): JsonResponse
    {
        return response()->json($call->load('status', 'academicYear', 'guidelineFile', 'proposals'));
    }

    public function update(UpdateCallRequest $request, Call $call): JsonResponse
    {
        $call->update($request->validated());
        return response()->json($call);
    }

    public function destroy(Call $call): JsonResponse
    {
        $call->delete();
        return response()->json(['message' => 'Call deleted.']);
    }
}
