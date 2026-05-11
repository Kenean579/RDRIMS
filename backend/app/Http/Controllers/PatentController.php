<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatentRequest;
use App\Http\Requests\UpdatePatentRequest;
use App\Models\Patent;
use Illuminate\Http\JsonResponse;

class PatentController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Patent::class);
        $patents = Patent::with(['status', 'project'])->latest()->paginate(20);
        return response()->json($patents);
    }

    public function store(StorePatentRequest $request): JsonResponse
    {
        $patent = Patent::create($request->validated());
        return response()->json($patent, 201);
    }

    public function show(Patent $patent): JsonResponse
    {
        $this->authorize('view', $patent);
        $patent->load(['status', 'project', 'licenses', 'files']);
        return response()->json($patent);
    }

    public function update(UpdatePatentRequest $request, Patent $patent): JsonResponse
    {
        $patent->update($request->validated());
        return response()->json($patent);
    }

    public function destroy(Patent $patent): JsonResponse
    {
        $this->authorize('delete', $patent);
        $patent->delete();
        return response()->json(null, 204);
    }
}