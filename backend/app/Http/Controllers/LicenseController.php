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
        return response()->json($patent->licenses);
    }

    public function store(StoreLicenseRequest $request, Patent $patent): JsonResponse
    {
        $license = $patent->licenses()->create($request->validated());
        return response()->json($license, 201);
    }

    public function show(License $license): JsonResponse
    {
        return response()->json($license->load('patent'));
    }

    public function update(UpdateLicenseRequest $request, License $license): JsonResponse
    {
        $this->authorize('update', $license);
        $license->update($request->validated());
        return response()->json($license);
    }

    public function destroy(License $license): JsonResponse
    {
        $this->authorize('delete', $license);
        $license->delete();
        return response()->json(['message' => 'License deleted.']);
    }
}