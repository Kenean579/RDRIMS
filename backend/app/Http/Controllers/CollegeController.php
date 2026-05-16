<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Http\Requests\CollegeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CollegeController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        return response()->json(College::all());
    }

    public function store(CollegeRequest $request): JsonResponse
    {
        $this->authorize('create', College::class);
        $college = College::create($request->validated());
        return response()->json($college, 201);
    }

    public function show(College $college): JsonResponse
    {
        return response()->json($college->load('departments'));
    }

    public function update(CollegeRequest $request, College $college): JsonResponse
    {
        $this->authorize('update', $college);
        $college->update($request->validated());
        return response()->json($college);
    }

    public function destroy(College $college): JsonResponse
    {
        $this->authorize('delete', $college);
        $college->delete();
        return response()->json(null, 204);
    }
}
