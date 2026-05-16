<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AcademicYearSubController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private AcademicYearService $academicYearService) {}

    public function setCurrent(AcademicYear $academicYear): JsonResponse
    {
        $this->authorize('update', $academicYear);
        $this->academicYearService->setCurrent($academicYear);
        return response()->json(['message' => 'Academic year set as current.']);
    }

    public function openCalls(AcademicYear $academicYear): JsonResponse
    {
        $this->authorize('update', $academicYear);
        $this->academicYearService->openCalls($academicYear);
        return response()->json(['message' => 'Calls opened for this academic year.']);
    }

    public function close(AcademicYear $academicYear): JsonResponse
    {
        $this->authorize('update', $academicYear);
        $this->academicYearService->close($academicYear);
        return response()->json(['message' => 'Academic year closed.']);
    }
}
