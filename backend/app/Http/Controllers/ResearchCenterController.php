<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResearchCenterRequest;
use App\Http\Requests\UpdateResearchCenterRequest;
use App\Models\ResearchCenter;
use Illuminate\Http\JsonResponse;

class ResearchCenterController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ResearchCenter::with('director', 'parentUniversity')->get());
    }

    public function store(StoreResearchCenterRequest $request): JsonResponse
    {
        $center = ResearchCenter::create($request->validated());
        return response()->json($center, 201);
    }

    public function show(ResearchCenter $researchCenter): JsonResponse
    {
        return response()->json($researchCenter->load('director', 'users', 'parentUniversity', 'parentCampus', 'parentFaculty'));
    }

    public function update(UpdateResearchCenterRequest $request, ResearchCenter $researchCenter): JsonResponse
    {
        $researchCenter->update($request->validated());
        return response()->json($researchCenter);
    }

    public function destroy(ResearchCenter $researchCenter): JsonResponse
    {
        $researchCenter->delete();
        return response()->json(['message' => 'Research center deleted.']);
    }
}
