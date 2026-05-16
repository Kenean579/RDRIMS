<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentResearchCenterController extends Controller
{
    public function index(Department $department): JsonResponse
    {
        return response()->json($department->researchCenters);
    }
}
