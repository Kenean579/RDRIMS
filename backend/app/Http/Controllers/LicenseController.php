<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLicenseRequest;
use App\Http\Requests\UpdateLicenseRequest;
use App\Models\License;
use App\Models\Patent;
use Illuminate\Http\JsonResponse;

class LicenseController extends Controller
{
    public function index(Patent $patent): JsonResponse
    {
        $this->authorize('view', $patent);
        $licenses = $patent->licenses()->get();
        return response()->json($licenses);
    }

    public function store(StoreLicenseRequest $request, Patent $patent): JsonResponse
    {
        $license = $patent->licenses()->create($request->validated());
        return response()->json($license, 201);
    }

    public function show(Patent $patent, License $license): JsonResponse
    {
        $this->authorize('view', $patent);
        return response()->json($license);
    }

    public function update(UpdateLicenseRequest $request, Patent $patent, License $license): JsonResponse
    {
        $license->update($request->validated());
        return response()->json($license);
    }

    public function destroy(Patent $patent, License $license): JsonResponse
    {
        $this->authorize('update', $patent);
        $license->delete();
        return response()->json(null, 204);
    }
}