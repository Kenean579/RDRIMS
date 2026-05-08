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
        $this->authorize('view', $partner);
        $moUs = $partner->moUs()->orderBy('start_date', 'desc')->get();
        return response()->json($moUs);
    }

    public function store(StoreMoURequest $request, Partner $partner): JsonResponse
    {
        $moU = $partner->moUs()->create($request->validated());
        return response()->json($moU, 201);
    }

    public function show(Partner $partner, MoU $moU): JsonResponse
    {
        $this->authorize('view', $partner);
        return response()->json($moU);
    }

    public function update(UpdateMoURequest $request, Partner $partner, MoU $moU): JsonResponse
    {
        $moU->update($request->validated());
        return response()->json($moU);
    }

    public function destroy(Partner $partner, MoU $moU): JsonResponse
    {
        $this->authorize('update', $partner);
        $moU->delete();
        return response()->json(null, 204);
    }
}