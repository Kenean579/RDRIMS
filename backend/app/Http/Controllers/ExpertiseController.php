<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpertiseRequest;
use App\Http\Requests\UpdateExpertiseRequest;
use App\Models\Expertise;
use Illuminate\Http\JsonResponse;

class ExpertiseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Expertise::all());
    }

    public function store(StoreExpertiseRequest $request): JsonResponse
    {
        $expertise = Expertise::create($request->validated());
        return response()->json($expertise, 201);
    }

    public function update(UpdateExpertiseRequest $request, Expertise $expertise): JsonResponse
    {
        $expertise->update($request->validated());
        return response()->json($expertise);
    }

    public function destroy(Expertise $expertise): JsonResponse
    {
        $expertise->delete();
        return response()->json(['message' => 'Expertise deleted.']);
    }
}
