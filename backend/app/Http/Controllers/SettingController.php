<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        // Public endpoint: no authorization required
        return response()->json(Setting::all());
    }

    public function store(StoreSettingRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Setting::class);
        $setting = Setting::create($request->validated());
        return response()->json($setting, 201);
    }

    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $this->authorize('update', $setting);
        $setting->update($request->validated());
        return response()->json($setting);
    }

    public function view(User $user, Setting $setting): bool
    {
        // Super admin can view all; institution admins can view scoped settings
        return $user->hasRole('super_admin') || $user->hasScopeForSetting($setting);
    }

    public function destroy(Setting $setting): JsonResponse
    {
        $this->authorize('delete', $setting);
        $setting->delete();
        return response()->json(['message' => 'Setting deleted.']);
    }

    public function bulk(Request $request): JsonResponse
    {
        // Bulk updates should be allowed for super admin only
        $this->authorize('create', Setting::class);
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            // Some system settings are intentionally optional and may be blank.
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($request->settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                ['value' => (string) ($settingData['value'] ?? '')]
            );
        }

        return response()->json(['message' => 'Settings updated successfully.']);
    }
}
?>
