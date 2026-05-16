<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Faculty;
use Illuminate\Http\JsonResponse;

class FacultyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Faculty::with('campus')->get());
    }

    public function store(StoreFacultyRequest $request): JsonResponse
    {
        $faculty = Faculty::create($request->validated());
        return response()->json($faculty, 201);
    }

    public function show(Faculty $faculty): JsonResponse
    {
        return response()->json($faculty->load('campus', 'departments'));
    }

    public function update(UpdateFacultyRequest $request, Faculty $faculty): JsonResponse
    {
        $faculty->update($request->validated());
        return response()->json($faculty);
    }

    public function destroy(Faculty $faculty): JsonResponse
    {
        $faculty->delete();
        return response()->json(['message' => 'Faculty deleted.']);
    }
}
