<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the settings edit form.
     *
     * Halaman ini hanya mengelola identitas aplikasi (nama perusahaan & nama
     * aplikasi). Konfigurasi iklan dikelola di halaman Iklan terpisah.
     */
    public function edit(): View
    {
        $setting = Setting::first() ?? new Setting([
            'applicationcompany' => '',
            'applicationname' => '',
        ]);

        return view('admin.settings.edit', [
            'setting' => $setting,
        ]);
    }

    /**
     * Update the application identity.
     *
     * Kolom iklan tidak disentuh di sini agar konfigurasi iklan tidak terhapus.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'applicationcompany' => ['required', 'string', 'max:100'],
            'applicationname' => ['required', 'string', 'max:255'],
        ], [
            'applicationcompany.required' => 'Nama perusahaan wajib diisi.',
            'applicationcompany.max' => 'Nama perusahaan maksimal 100 karakter.',
            'applicationname.required' => 'Nama aplikasi wajib diisi.',
            'applicationname.max' => 'Nama aplikasi maksimal 255 karakter.',
        ]);

        $setting = Setting::first();

        if ($setting) {
            $setting->update($validated);
        } else {
            Setting::create($validated);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }
}
