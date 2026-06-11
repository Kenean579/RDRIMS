<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMoURequest;
use App\Http\Requests\UpdateMoURequest;
use App\Models\MoU;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;

class MoUController extends Controller
{
    public function index(Partner $partner): JsonResponse
    {
        return response()->json($partner->moUs);
    }

    public function store(StoreMoURequest $request, Partner $partner): JsonResponse
    {
        $moU = $partner->moUs()->create($request->validated());
        return response()->json($moU, 201);
    }

    public function show(MoU $moU): JsonResponse
    {
        return response()->json($moU->load('partner', 'researchCenter'));
    }

    public function update(UpdateMoURequest $request, MoU $moU): JsonResponse
    {
        $moU->update($request->validated());
        return response()->json($moU);
    }

    public function destroy(MoU $moU): JsonResponse
    {
        $moU->delete();
        return response()->json(['message' => 'MoU deleted.']);
    }
}