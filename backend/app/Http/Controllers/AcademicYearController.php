<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;

class AcademicYearController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AcademicYear::orderBy('start_date', 'desc')->get());
    }

    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $year = AcademicYear::create($request->validated());
        return response()->json($year, 201);
    }

    public function show(AcademicYear $academicYear): JsonResponse
    {
        return response()->json($academicYear);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $academicYear->update($request->validated());
        return response()->json($academicYear);
    }

    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        $academicYear->delete();
        return response()->json(['message' => 'Academic year deleted.']);
    }

    public function setCurrent(AcademicYear $academicYear): JsonResponse
    {
        AcademicYear::query()->update(['is_current' => false]);
        $academicYear->update(['is_current' => true]);
        return response()->json(['message' => 'Current academic year set.']);
    }
}
