<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUniversityRequest;
use App\Http\Requests\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\JsonResponse;

class UniversityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(University::with('campuses')->get());
    }

    public function store(StoreUniversityRequest $request): JsonResponse
    {
        $university = University::create($request->validated());
        return response()->json($university, 201);
    }

    public function show(University $university): JsonResponse
    {
        return response()->json($university->load('campuses.faculties.departments', 'researchCenters'));
    }

    public function update(UpdateUniversityRequest $request, University $university): JsonResponse
    {
        $university->update($request->validated());
        return response()->json($university);
    }

    public function destroy(University $university): JsonResponse
    {
        $university->delete();
        return response()->json(['message' => 'University deleted.']);
    }
}
