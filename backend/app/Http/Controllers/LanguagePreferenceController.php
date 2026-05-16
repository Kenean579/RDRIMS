<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguagePreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $preference = $request->user()->languagePreference;
        return response()->json($preference ?? ['locale' => 'en']);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate(['locale' => 'required|in:en,am']);

        $preference = $request->user()->languagePreference()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['locale' => $request->locale]
        );

        return response()->json($preference);
    }
}
