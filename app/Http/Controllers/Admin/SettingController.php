<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Kategori yang sudah ditampilkan sebagai menu, sehingga tidak perlu
     * muncul lagi di daftar Visibilitas Kategori.
     */
    private const MENU_CATEGORY_ID = 1;

    /**
     * Display the settings edit form.
     *
     * Halaman ini hanya mengelola identitas aplikasi (nama perusahaan & nama
     * aplikasi) dan visibilitas menu. Konfigurasi iklan dikelola di halaman
     * Iklan terpisah.
     */
    public function edit(): View
    {
        $setting = Setting::first() ?? new Setting([
            'applicationcompany' => '',
            'applicationname' => '',
            'applicationmusikactive' => 'Y',
            'applicationlyricactive' => 'Y',
            'applicationmenuindonesiaactive' => 'Y',
            'applicationmusiklabel' => 'Menu Musik',
            'applicationlyriclabel' => 'Menu Lirik',
            'applicationmenuindonesialabel' => 'Menu Indonesia',
        ]);

        return view('admin.settings.edit', [
            'setting' => $setting,
            'categories' => Category::where('songcategoryid', '!=', self::MENU_CATEGORY_ID)
                ->orderBy('sort_order')
                ->orderBy('songcategoryname')
                ->get(),
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
            'applicationmusikactive' => ['sometimes', 'required', 'in:Y,N'],
            'applicationlyricactive' => ['sometimes', 'required', 'in:Y,N'],
            'applicationmenuindonesiaactive' => ['sometimes', 'required', 'in:Y,N'],
            'applicationmusiklabel' => ['sometimes', 'required', 'string', 'max:50'],
            'applicationlyriclabel' => ['sometimes', 'required', 'string', 'max:50'],
            'applicationmenuindonesialabel' => ['sometimes', 'required', 'string', 'max:50'],
            'category_visibility' => ['nullable', 'array'],
            'category_visibility.*' => ['required', 'in:Y,N'],
        ], [
            'applicationcompany.required' => 'Nama perusahaan wajib diisi.',
            'applicationcompany.max' => 'Nama perusahaan maksimal 100 karakter.',
            'applicationname.required' => 'Nama aplikasi wajib diisi.',
            'applicationname.max' => 'Nama aplikasi maksimal 255 karakter.',
            'applicationmusikactive.required' => 'Status menu Musik wajib dipilih.',
            'applicationmusikactive.in' => 'Status menu Musik harus bernilai Y atau N.',
            'applicationlyricactive.required' => 'Status menu Lirik wajib dipilih.',
            'applicationlyricactive.in' => 'Status menu Lirik harus bernilai Y atau N.',
            'applicationmenuindonesiaactive.required' => 'Status menu Indonesia wajib dipilih.',
            'applicationmenuindonesiaactive.in' => 'Status menu Indonesia harus bernilai Y atau N.',
            'category_visibility.*.required' => 'Status visibilitas kategori wajib dipilih.',
            'category_visibility.*.in' => 'Status visibilitas kategori harus bernilai Y atau N.',
        ]);

        $categoryVisibility = $validated['category_visibility'] ?? [];
        unset($validated['category_visibility']);

        $setting = Setting::first();

        if ($setting) {
            $setting->update($validated);
        } else {
            Setting::create($validated);
        }

        foreach ($categoryVisibility as $categoryId => $status) {
            Category::whereKey($categoryId)
                ->whereKeyNot(self::MENU_CATEGORY_ID)
                ->update([
                    'is_active' => $status === 'Y',
                ]);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }
}
