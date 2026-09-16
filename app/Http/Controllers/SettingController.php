<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the current application settings.
     */
    public function index(): JsonResponse
    {
        $setting = Setting::first();

        return response()->json([
            'data' => $setting,
        ]);
    }

    /**
     * Store or update the application settings.
     */
    public function save(Request $request): JsonResponse
    {
        $setting = Setting::first();
        $isNew = ! $setting;

        $validated = $request->validate([
            'applicationcompany' => [$isNew ? 'required' : 'sometimes', 'string', 'max:100'],
            'applicationname' => [$isNew ? 'required' : 'sometimes', 'string', 'max:255'],
            'applicationads1' => ['nullable', 'string'],
            'applicationads2' => ['nullable', 'string'],
            'applicationadsactive' => ['sometimes', 'string', 'in:Y,N'],
            'applicationadsbottom' => ['nullable', 'string'],
            'applicationadsbottomactive' => ['sometimes', 'string', 'in:Y,N'],
        ]);

        if ($isNew) {
            $setting = Setting::create($validated);

            return response()->json([
                'message' => 'Settings created successfully',
                'data' => $setting,
            ], 201);
        }

        $setting->update($validated);

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $setting->fresh(),
        ]);
    }

    /**
     * Display the specified setting by ID.
     */
    public function show(Setting $setting): JsonResponse
    {
        return response()->json([
            'data' => $setting,
        ]);
    }

    /**
     * Update the specified setting by ID.
     */
    public function update(Request $request, Setting $setting): JsonResponse
    {
        $validated = $request->validate([
            'applicationcompany' => ['sometimes', 'required', 'string', 'max:100'],
            'applicationname' => ['sometimes', 'required', 'string', 'max:255'],
            'applicationads1' => ['nullable', 'string'],
            'applicationads2' => ['nullable', 'string'],
            'applicationadsactive' => ['sometimes', 'required', 'string', 'in:Y,N'],
            'applicationadsbottom' => ['nullable', 'string'],
            'applicationadsbottomactive' => ['sometimes', 'required', 'string', 'in:Y,N'],
        ]);

        $setting->update($validated);

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $setting->fresh(),
        ]);
    }

    /**
     * Remove the specified setting from storage.
     */
    public function destroy(Setting $setting): JsonResponse
    {
        $setting->delete();

        return response()->json([
            'message' => 'Settings deleted successfully',
        ]);
    }
}
