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
            'applicationads1type' => ['nullable', 'string', 'in:image,admob_native,admob_banner,admob_interstitial,admob_app_open,admob_reward'],
            'applicationads1unit' => ['nullable', 'string', 'max:255'],
            'applicationads1active' => ['sometimes', 'string', 'in:Y,N'],
            'applicationads1clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationads2' => ['nullable', 'string'],
            'applicationads2type' => ['nullable', 'string', 'in:image,admob_native,admob_banner,admob_interstitial,admob_app_open,admob_reward'],
            'applicationads2unit' => ['nullable', 'string', 'max:255'],
            'applicationads2active' => ['sometimes', 'string', 'in:Y,N'],
            'applicationads2clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadsactive' => ['sometimes', 'string', 'in:Y,N'],
            'applicationadsbottom' => ['nullable', 'string'],
            'applicationadsbottomtype' => ['nullable', 'string', 'in:image,admob_native,admob_banner,admob_interstitial,admob_app_open,admob_reward'],
            'applicationadsbottomunit' => ['nullable', 'string', 'max:255'],
            'applicationadsbottomactive' => ['sometimes', 'string', 'in:Y,N'],
            'applicationadsbottomclickurl' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        $validated = $this->syncActiveStatuses($validated, $setting);

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
     * Jaga konsistensi status per slot dengan kolom agregat lama.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function syncActiveStatuses(array $validated, ?Setting $setting): array
    {
        $hasSlotStatus = array_key_exists('applicationads1active', $validated)
            || array_key_exists('applicationads2active', $validated);

        if ($hasSlotStatus) {
            $slot1 = $validated['applicationads1active'] ?? $setting?->applicationads1active ?? 'Y';
            $slot2 = $validated['applicationads2active'] ?? $setting?->applicationads2active ?? 'Y';

            $validated['applicationadsactive'] = ($slot1 === 'Y' || $slot2 === 'Y') ? 'Y' : 'N';
        } elseif (array_key_exists('applicationadsactive', $validated)) {
            // Klien lama hanya mengirim status global: terapkan ke kedua slot.
            $validated['applicationads1active'] = $validated['applicationadsactive'];
            $validated['applicationads2active'] = $validated['applicationadsactive'];
        }

        return $validated;
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
            'applicationads1type' => ['nullable', 'string', 'in:image,admob_native,admob_banner,admob_interstitial,admob_app_open,admob_reward'],
            'applicationads1unit' => ['nullable', 'string', 'max:255'],
            'applicationads1active' => ['sometimes', 'string', 'in:Y,N'],
            'applicationads1clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationads2' => ['nullable', 'string'],
            'applicationads2type' => ['nullable', 'string', 'in:image,admob_native,admob_banner,admob_interstitial,admob_app_open,admob_reward'],
            'applicationads2unit' => ['nullable', 'string', 'max:255'],
            'applicationads2active' => ['sometimes', 'string', 'in:Y,N'],
            'applicationads2clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadsactive' => ['sometimes', 'required', 'string', 'in:Y,N'],
            'applicationadsbottom' => ['nullable', 'string'],
            'applicationadsbottomtype' => ['nullable', 'string', 'in:image,admob_native,admob_banner,admob_interstitial,admob_app_open,admob_reward'],
            'applicationadsbottomunit' => ['nullable', 'string', 'max:255'],
            'applicationadsbottomactive' => ['sometimes', 'required', 'string', 'in:Y,N'],
            'applicationadsbottomclickurl' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        $validated = $this->syncActiveStatuses($validated, $setting);

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
