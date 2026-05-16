<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);
        return response()->json(Setting::all());
    }

    public function store(StoreSettingRequest $request): JsonResponse
    {
        $setting = Setting::create($request->validated());
        return response()->json($setting, 201);
    }

    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $this->authorize('update', $setting);
        $setting->update($request->validated());
        return response()->json($setting);
    }

    public function destroy(Setting $setting): JsonResponse
    {
        $this->authorize('delete', $setting);
        $setting->delete();
        return response()->json(['message' => 'Setting deleted.']);
    }
}
