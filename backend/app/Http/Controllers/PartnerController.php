<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;

class PartnerController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Partner::class);
        $partners = Partner::withCount('moUs')->latest()->paginate(20);
        return response()->json($partners);
    }

    public function store(StorePartnerRequest $request): JsonResponse
    {
        $partner = Partner::create($request->validated());
        return response()->json($partner, 201);
    }

    public function show(Partner $partner): JsonResponse
    {
        $this->authorize('view', $partner);
        $partner->load('moUs');
        return response()->json($partner);
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): JsonResponse
    {
        $partner->update($request->validated());
        return response()->json($partner);
    }

    public function destroy(Partner $partner): JsonResponse
    {
        $this->authorize('delete', $partner);
        $partner->delete();
        return response()->json(null, 204);
    }
}