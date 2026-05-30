<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThematicArea;
use Illuminate\Http\JsonResponse;

class ThematicAreaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ThematicArea::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $thematicArea = ThematicArea::create($validated);
        return response()->json($thematicArea, 201);
    }

    public function show($id): JsonResponse
    {
        return response()->json(ThematicArea::findOrFail($id));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $thematicArea = ThematicArea::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string'
        ]);

        $thematicArea->update($validated);
        return response()->json($thematicArea);
    }

    public function destroy($id): JsonResponse
    {
        ThematicArea::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
