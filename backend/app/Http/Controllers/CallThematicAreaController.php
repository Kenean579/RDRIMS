<?php

namespace App\Http\Controllers;

use App\Models\Call;
use Illuminate\Http\JsonResponse;

class CallThematicAreaController extends Controller
{
    public function index(Call $call): JsonResponse
    {
        return response()->json($call->academicYear->thematicAreas);
    }
}
