<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the settings edit form.
     */
    public function edit(): View
    {
        $setting = Setting::first() ?? new Setting([
            'applicationcompany' => '',
            'applicationname' => '',
            'applicationads1' => '',
            'applicationads1type' => 'image',
            'applicationads1unit' => '',
            'applicationads2' => '',
            'applicationads2type' => 'image',
            'applicationads2unit' => '',
            'applicationadsactive' => 'Y',
            'applicationadsbottom' => '',
            'applicationadsbottomtype' => 'image',
            'applicationadsbottomunit' => '',
            'applicationadsbottomactive' => 'Y',
        ]);

        return view('admin.settings.edit', [
            'setting' => $setting,
        ]);
    }

    /**
     * Update the application settings.
     */
    public function update(Request $request): RedirectResponse
    {
        // Jenis iklan yang didukung untuk setiap slot. `image` memakai unggahan
        // berkas, sisanya memakai Ad Unit ID AdMob.
        $admobTypes = 'admob_native,admob_banner,admob_interstitial,admob_app_open,admob_reward';

        $validated = $request->validate([
            'applicationcompany' => ['required', 'string', 'max:100'],
            'applicationname' => ['required', 'string', 'max:255'],
            'applicationads1' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationads1type' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationads1unit' => ['nullable', 'string', 'max:255', 'required_if:applicationads1type,'.$admobTypes],
            'applicationads2' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationads2type' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationads2unit' => ['nullable', 'string', 'max:255', 'required_if:applicationads2type,'.$admobTypes],
            'applicationadsactive' => ['required', 'in:Y,N'],
            'applicationadsbottom' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadsbottomtype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadsbottomunit' => ['nullable', 'string', 'max:255', 'required_if:applicationadsbottomtype,'.$admobTypes],
            'applicationadsbottomactive' => ['required', 'in:Y,N'],
            'delete_applicationads1' => ['nullable', 'boolean'],
            'delete_applicationads2' => ['nullable', 'boolean'],
            'delete_applicationadsbottom' => ['nullable', 'boolean'],
        ], [
            'applicationcompany.required' => 'Nama perusahaan wajib diisi.',
            'applicationcompany.max' => 'Nama perusahaan maksimal 100 karakter.',
            'applicationname.required' => 'Nama aplikasi wajib diisi.',
            'applicationname.max' => 'Nama aplikasi maksimal 255 karakter.',
            'applicationads1.image' => 'Banner iklan slot 1 harus berupa file gambar.',
            'applicationads1.max' => 'Ukuran file banner iklan slot 1 maksimal 2MB.',
            'applicationads1type.in' => 'Jenis iklan slot 1 tidak dikenali.',
            'applicationads1unit.required_if' => 'Ad Unit ID AdMob slot 1 wajib diisi.',
            'applicationads2.image' => 'Banner iklan slot 2 harus berupa file gambar.',
            'applicationads2.max' => 'Ukuran file banner iklan slot 2 maksimal 2MB.',
            'applicationads2type.in' => 'Jenis iklan slot 2 tidak dikenali.',
            'applicationads2unit.required_if' => 'Ad Unit ID AdMob slot 2 wajib diisi.',
            'applicationadsbottom.image' => 'Banner iklan bawah harus berupa file gambar.',
            'applicationadsbottom.max' => 'Ukuran file banner iklan bawah maksimal 2MB.',
            'applicationadsbottomtype.in' => 'Jenis iklan bawah tidak dikenali.',
            'applicationadsbottomunit.required_if' => 'Ad Unit ID AdMob bawah wajib diisi.',
            'applicationadsactive.required' => 'Status iklan utama wajib dipilih.',
            'applicationadsactive.in' => 'Status iklan utama harus bernilai Y atau N.',
            'applicationadsbottomactive.required' => 'Status iklan bawah wajib dipilih.',
            'applicationadsbottomactive.in' => 'Status iklan bawah harus bernilai Y atau N.',
        ]);

        $setting = Setting::first();

        // Normalisasi nilai default jenis iklan bila tidak dikirim oleh form.
        foreach (['applicationads1type', 'applicationads2type', 'applicationadsbottomtype'] as $typeField) {
            if (empty($validated[$typeField])) {
                $validated[$typeField] = $setting?->$typeField ?? 'image';
            }
        }

        $adFields = ['applicationads1', 'applicationads2', 'applicationadsbottom'];
        foreach ($adFields as $field) {
            if ($request->hasFile($field)) {
                if ($setting && $setting->$field && ! filter_var($setting->$field, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($setting->$field)) {
                    Storage::disk('public')->delete($setting->$field);
                }
                $validated[$field] = $request->file($field)->store('banners', 'public');
            } elseif ($request->boolean("delete_{$field}")) {
                if ($setting && $setting->$field && ! filter_var($setting->$field, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($setting->$field)) {
                    Storage::disk('public')->delete($setting->$field);
                }
                $validated[$field] = null;
            } else {
                $validated[$field] = $setting?->$field;
            }

            unset($validated["delete_{$field}"]);
        }

        if ($setting) {
            $setting->update($validated);
        } else {
            Setting::create($validated);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }
}
