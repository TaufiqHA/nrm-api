<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdSettingController extends Controller
{
    /**
     * Jenis iklan AdMob yang didukung (selain `image`).
     */
    private const ADMOB_TYPES = 'admob_native,admob_banner,admob_interstitial,admob_app_open,admob_reward';

    /**
     * Display the ad configuration form.
     */
    public function edit(): View
    {
        $setting = Setting::first() ?? new Setting([
            'applicationcompany' => '',
            'applicationname' => '',
            'applicationads1' => '',
            'applicationads1type' => 'image',
            'applicationads1unit' => '',
            'applicationads1active' => 'Y',
            'applicationads1clickurl' => '',
            'applicationads2' => '',
            'applicationads2type' => 'image',
            'applicationads2unit' => '',
            'applicationads2active' => 'Y',
            'applicationads2clickurl' => '',
            'applicationadsactive' => 'Y',
            'applicationadsbottom' => '',
            'applicationadsbottomtype' => 'image',
            'applicationadsbottomunit' => '',
            'applicationadsbottomactive' => 'Y',
            'applicationadsbottomclickurl' => '',
        ]);

        return view('admin.ads.edit', [
            'setting' => $setting,
        ]);
    }

    /**
     * Update the ad configuration (slot 1, slot 2, dan banner bawah).
     *
     * Field identitas aplikasi (nama perusahaan & nama aplikasi) dikelola di
     * halaman Pengaturan dan sengaja tidak disentuh di sini.
     */
    public function update(Request $request): RedirectResponse
    {
        $admobTypes = self::ADMOB_TYPES;

        $validated = $request->validate([
            'applicationads1' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationads1type' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationads1unit' => ['nullable', 'string', 'max:255', 'required_if:applicationads1type,'.$admobTypes],
            'applicationads1active' => ['required', 'in:Y,N'],
            'applicationads1clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationads2' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationads2type' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationads2unit' => ['nullable', 'string', 'max:255', 'required_if:applicationads2type,'.$admobTypes],
            'applicationads2active' => ['required', 'in:Y,N'],
            'applicationads2clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadsbottom' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadsbottomtype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadsbottomunit' => ['nullable', 'string', 'max:255', 'required_if:applicationadsbottomtype,'.$admobTypes],
            'applicationadsbottomactive' => ['required', 'in:Y,N'],
            'applicationadsbottomclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'delete_applicationads1' => ['nullable', 'boolean'],
            'delete_applicationads2' => ['nullable', 'boolean'],
            'delete_applicationadsbottom' => ['nullable', 'boolean'],
        ], [
            'applicationads1.image' => 'Banner iklan slot 1 harus berupa file gambar.',
            'applicationads1.max' => 'Ukuran file banner iklan slot 1 maksimal 2MB.',
            'applicationads1type.in' => 'Jenis iklan slot 1 tidak dikenali.',
            'applicationads1unit.required_if' => 'Ad Unit ID AdMob slot 1 wajib diisi.',
            'applicationads1active.required' => 'Status iklan slot 1 wajib dipilih.',
            'applicationads1active.in' => 'Status iklan slot 1 harus bernilai Y atau N.',
            'applicationads1clickurl.url' => 'URL tujuan banner slot 1 harus berupa URL yang valid.',
            'applicationads1clickurl.max' => 'URL tujuan banner slot 1 maksimal 2048 karakter.',
            'applicationads2.image' => 'Banner iklan slot 2 harus berupa file gambar.',
            'applicationads2.max' => 'Ukuran file banner iklan slot 2 maksimal 2MB.',
            'applicationads2type.in' => 'Jenis iklan slot 2 tidak dikenali.',
            'applicationads2unit.required_if' => 'Ad Unit ID AdMob slot 2 wajib diisi.',
            'applicationads2active.required' => 'Status iklan slot 2 wajib dipilih.',
            'applicationads2active.in' => 'Status iklan slot 2 harus bernilai Y atau N.',
            'applicationads2clickurl.url' => 'URL tujuan banner slot 2 harus berupa URL yang valid.',
            'applicationads2clickurl.max' => 'URL tujuan banner slot 2 maksimal 2048 karakter.',
            'applicationadsbottom.image' => 'Banner iklan bawah harus berupa file gambar.',
            'applicationadsbottom.max' => 'Ukuran file banner iklan bawah maksimal 2MB.',
            'applicationadsbottomtype.in' => 'Jenis iklan bawah tidak dikenali.',
            'applicationadsbottomunit.required_if' => 'Ad Unit ID AdMob bawah wajib diisi.',
            'applicationadsbottomactive.required' => 'Status iklan bawah wajib dipilih.',
            'applicationadsbottomactive.in' => 'Status iklan bawah harus bernilai Y atau N.',
            'applicationadsbottomclickurl.url' => 'URL tujuan banner bawah harus berupa URL yang valid.',
            'applicationadsbottomclickurl.max' => 'URL tujuan banner bawah maksimal 2048 karakter.',
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
                $validated[$field.'clickurl'] = null;
            } else {
                $validated[$field] = $setting?->$field;
            }

            unset($validated["delete_{$field}"]);
        }

        // Kolom lama dipakai aplikasi versi sebelumnya: tampilkan iklan utama
        // selama salah satu slot utamanya masih aktif.
        $validated['applicationadsactive'] = ($validated['applicationads1active'] === 'Y' || $validated['applicationads2active'] === 'Y')
            ? 'Y'
            : 'N';

        if ($setting) {
            $setting->update($validated);
        } else {
            // Kolom identitas bersifat NOT NULL; buat nilai kosong bila belum ada
            // record aplikasi (identitas diisi nanti melalui halaman Pengaturan).
            Setting::create($validated + [
                'applicationcompany' => '',
                'applicationname' => '',
            ]);
        }

        return redirect()->route('admin.ads.edit')->with('success', 'Konfigurasi iklan berhasil disimpan.');
    }
}
