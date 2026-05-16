<?php

namespace App\Http\Controllers;

use App\Models\College;
use Illuminate\Http\JsonResponse;

class CollegeDepartmentController extends Controller
{
    public function index(College $college): JsonResponse
    {
        return response()->json($college->departments);
    }
}
