<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserExpertiseController extends Controller
{
    public function attach(Request $request, User $user): JsonResponse
    {
        $request->validate(['expertise_id' => 'required|exists:expertise,id']);
        $user->expertise()->attach($request->expertise_id);
        return response()->json(['message' => 'Expertise attached.']);
    }

    public function detach(Request $request, User $user): JsonResponse
    {
        $request->validate(['expertise_id' => 'required|exists:expertise,id']);
        $user->expertise()->detach($request->expertise_id);
        return response()->json(['message' => 'Expertise detached.']);
    }
}
