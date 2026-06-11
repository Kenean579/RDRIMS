<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampusRequest;
use App\Http\Requests\UpdateCampusRequest;
use App\Models\Campus;
use Illuminate\Http\JsonResponse;

class CampusController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Campus::with('university', 'logoFile')->get());
    }

    public function store(StoreCampusRequest $request): JsonResponse
    {
        $campus = Campus::create($request->validated());
        return response()->json($campus, 201);
    }

    public function show(Campus $campus): JsonResponse
    {
        return response()->json($campus->load('university', 'faculties'));
    }

    public function update(UpdateCampusRequest $request, Campus $campus): JsonResponse
    {
        $campus->update($request->validated());
        return response()->json($campus);
    }

    public function destroy(Campus $campus): JsonResponse
    {
        $campus->delete();
        return response()->json(['message' => 'Campus deleted.']);
    }
}
