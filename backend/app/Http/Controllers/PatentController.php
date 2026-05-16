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
        return response()->json(Patent::with('status', 'project')->paginate(20));
    }

    public function store(StorePatentRequest $request): JsonResponse
    {
        $patent = Patent::create($request->validated());
        return response()->json($patent, 201);
    }

    public function show(Patent $patent): JsonResponse
    {
        return response()->json($patent->load('status', 'project', 'licenses', 'files'));
    }

    public function update(UpdatePatentRequest $request, Patent $patent): JsonResponse
    {
        $this->authorize('update', $patent);
        $patent->update($request->validated());
        return response()->json($patent);
    }

    public function destroy(Patent $patent): JsonResponse
    {
        $this->authorize('delete', $patent);
        $patent->delete();
        return response()->json(['message' => 'Patent deleted.']);
    }
}