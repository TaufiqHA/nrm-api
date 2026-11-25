<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAd;
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
            'applicationadshome' => '',
            'applicationadshometype' => 'image',
            'applicationadshomeunit' => '',
            'applicationadshomeactive' => 'Y',
            'applicationadshomeclickurl' => '',
            'applicationadshometop' => '',
            'applicationadshometoptype' => 'image',
            'applicationadshometopunit' => '',
            'applicationadshometopactive' => 'Y',
            'applicationadshometopclickurl' => '',
            'applicationadshomebottom' => '',
            'applicationadshomebottomtype' => 'image',
            'applicationadshomebottomunit' => '',
            'applicationadshomebottomactive' => 'Y',
            'applicationadshomebottomclickurl' => '',
            'applicationadslyric' => '',
            'applicationadslyrictype' => 'image',
            'applicationadslyricunit' => '',
            'applicationadslyricactive' => 'Y',
            'applicationadslyricclickurl' => '',
            'applicationadslyrictop' => '',
            'applicationadslyrictoptype' => 'image',
            'applicationadslyrictopunit' => '',
            'applicationadslyrictopactive' => 'Y',
            'applicationadslyrictopclickurl' => '',
            'applicationadslyricbottom' => '',
            'applicationadslyricbottomtype' => 'image',
            'applicationadslyricbottomunit' => '',
            'applicationadslyricbottomactive' => 'Y',
            'applicationadslyricbottomclickurl' => '',
            'applicationadsmusiktop' => '',
            'applicationadsmusiktoptype' => 'image',
            'applicationadsmusiktopunit' => '',
            'applicationadsmusiktopactive' => 'Y',
            'applicationadsmusiktopclickurl' => '',
            'applicationadsmusik' => '',
            'applicationadsmusiktype' => 'image',
            'applicationadsmusikunit' => '',
            'applicationadsmusikactive' => 'Y',
            'applicationadsmusikclickurl' => '',
            'applicationadsmusikbottom' => '',
            'applicationadsmusikbottomtype' => 'image',
            'applicationadsmusikbottomunit' => '',
            'applicationadsmusikbottomactive' => 'Y',
            'applicationadsmusikbottomclickurl' => '',
            'applicationadsactive' => 'Y',
            'applicationadsbottom' => '',
            'applicationadsbottomtype' => 'image',
            'applicationadsbottomunit' => '',
            'applicationadsbottomactive' => 'Y',
            'applicationadsbottomclickurl' => '',
        ]);

        return view('admin.ads.edit', [
            'setting' => $setting,
            'categories' => Category::with('adSlots')
                ->orderBy('sort_order')
                ->orderBy('songcategoryname')
                ->get(),
        ]);
    }

    /**
     * Update the ad configuration (slot 1, slot 2, halaman utama, halaman lirik,
     * halaman BUKU MERAH (Musik), dan banner bawah).
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
            'applicationads1unit' => ['nullable', 'string', 'max:255'],
            'applicationads1active' => ['required', 'in:Y,N'],
            'applicationads1clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationads2' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationads2type' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationads2unit' => ['nullable', 'string', 'max:255'],
            'applicationads2active' => ['required', 'in:Y,N'],
            'applicationads2clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadshome' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadshometype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadshomeunit' => ['nullable', 'string', 'max:255'],
            'applicationadshomeactive' => ['nullable', 'in:Y,N'],
            'applicationadshomeclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadshometop' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadshometoptype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadshometopunit' => ['nullable', 'string', 'max:255'],
            'applicationadshometopactive' => ['nullable', 'in:Y,N'],
            'applicationadshometopclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadshomebottom' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadshomebottomtype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadshomebottomunit' => ['nullable', 'string', 'max:255'],
            'applicationadshomebottomactive' => ['nullable', 'in:Y,N'],
            'applicationadshomebottomclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadslyric' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadslyrictype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadslyricunit' => ['nullable', 'string', 'max:255'],
            'applicationadslyricactive' => ['nullable', 'in:Y,N'],
            'applicationadslyricclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadslyrictop' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadslyrictoptype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadslyrictopunit' => ['nullable', 'string', 'max:255'],
            'applicationadslyrictopactive' => ['nullable', 'in:Y,N'],
            'applicationadslyrictopclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadslyricbottom' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadslyricbottomtype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadslyricbottomunit' => ['nullable', 'string', 'max:255'],
            'applicationadslyricbottomactive' => ['nullable', 'in:Y,N'],
            'applicationadslyricbottomclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadsmusiktop' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadsmusiktoptype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadsmusiktopunit' => ['nullable', 'string', 'max:255'],
            'applicationadsmusiktopactive' => ['nullable', 'in:Y,N'],
            'applicationadsmusiktopclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadsmusik' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadsmusiktype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadsmusikunit' => ['nullable', 'string', 'max:255'],
            'applicationadsmusikactive' => ['nullable', 'in:Y,N'],
            'applicationadsmusikclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadsmusikbottom' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadsmusikbottomtype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadsmusikbottomunit' => ['nullable', 'string', 'max:255'],
            'applicationadsmusikbottomactive' => ['nullable', 'in:Y,N'],
            'applicationadsmusikbottomclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'applicationadsbottom' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'applicationadsbottomtype' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'applicationadsbottomunit' => ['nullable', 'string', 'max:255'],
            'applicationadsbottomactive' => ['required', 'in:Y,N'],
            'applicationadsbottomclickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'delete_applicationads1' => ['nullable', 'boolean'],
            'delete_applicationads2' => ['nullable', 'boolean'],
            'delete_applicationadshome' => ['nullable', 'boolean'],
            'delete_applicationadshometop' => ['nullable', 'boolean'],
            'delete_applicationadshomebottom' => ['nullable', 'boolean'],
            'delete_applicationadslyric' => ['nullable', 'boolean'],
            'delete_applicationadslyrictop' => ['nullable', 'boolean'],
            'delete_applicationadslyricbottom' => ['nullable', 'boolean'],
            'delete_applicationadsmusiktop' => ['nullable', 'boolean'],
            'delete_applicationadsmusik' => ['nullable', 'boolean'],
            'delete_applicationadsmusikbottom' => ['nullable', 'boolean'],
            'delete_applicationadsbottom' => ['nullable', 'boolean'],
            'category_ads' => ['nullable', 'array'],
            'category_ads.*' => ['array'],
            'category_ads.*.*.image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:2048'],
            'category_ads.*.*.type' => ['nullable', 'string', 'in:image,'.$admobTypes],
            'category_ads.*.*.unit' => ['nullable', 'string', 'max:255'],
            'category_ads.*.*.active' => ['required', 'in:Y,N'],
            'category_ads.*.*.clickurl' => ['nullable', 'url:http,https', 'max:2048'],
            'category_ads.*.*.delete_image' => ['nullable', 'boolean'],
        ], [
            'applicationads1.image' => 'Banner iklan slot 1 harus berupa file gambar.',
            'applicationads1.max' => 'Ukuran file banner iklan slot 1 maksimal 2MB.',
            'applicationads1type.in' => 'Jenis iklan slot 1 tidak dikenali.',
            'applicationads1active.required' => 'Status iklan slot 1 wajib dipilih.',
            'applicationads1active.in' => 'Status iklan slot 1 harus bernilai Y atau N.',
            'applicationads1clickurl.url' => 'URL tujuan banner slot 1 harus berupa URL yang valid.',
            'applicationads1clickurl.max' => 'URL tujuan banner slot 1 maksimal 2048 karakter.',
            'applicationads2.image' => 'Banner iklan slot 2 harus berupa file gambar.',
            'applicationads2.max' => 'Ukuran file banner iklan slot 2 maksimal 2MB.',
            'applicationads2type.in' => 'Jenis iklan slot 2 tidak dikenali.',
            'applicationads2active.required' => 'Status iklan slot 2 wajib dipilih.',
            'applicationads2active.in' => 'Status iklan slot 2 harus bernilai Y atau N.',
            'applicationads2clickurl.url' => 'URL tujuan banner slot 2 harus berupa URL yang valid.',
            'applicationads2clickurl.max' => 'URL tujuan banner slot 2 maksimal 2048 karakter.',
            'applicationadshome.image' => 'Banner iklan halaman utama harus berupa file gambar.',
            'applicationadshome.max' => 'Ukuran file banner iklan halaman utama maksimal 2MB.',
            'applicationadshometype.in' => 'Jenis iklan halaman utama tidak dikenali.',
            'applicationadshomeactive.in' => 'Status iklan halaman utama harus bernilai Y atau N.',
            'applicationadshomeclickurl.url' => 'URL tujuan banner halaman utama harus berupa URL yang valid.',
            'applicationadshomeclickurl.max' => 'URL tujuan banner halaman utama maksimal 2048 karakter.',
            'applicationadshometop.image' => 'Banner atas halaman utama harus berupa file gambar.',
            'applicationadshometop.max' => 'Ukuran file banner atas halaman utama maksimal 2MB.',
            'applicationadshometoptype.in' => 'Jenis iklan atas halaman utama tidak dikenali.',
            'applicationadshometopactive.in' => 'Status iklan atas halaman utama harus bernilai Y atau N.',
            'applicationadshometopclickurl.url' => 'URL tujuan banner atas halaman utama harus berupa URL yang valid.',
            'applicationadshometopclickurl.max' => 'URL tujuan banner atas halaman utama maksimal 2048 karakter.',
            'applicationadshomebottom.image' => 'Banner bawah halaman utama harus berupa file gambar.',
            'applicationadshomebottom.max' => 'Ukuran file banner bawah halaman utama maksimal 2MB.',
            'applicationadshomebottomtype.in' => 'Jenis iklan bawah halaman utama tidak dikenali.',
            'applicationadshomebottomactive.in' => 'Status iklan bawah halaman utama harus bernilai Y atau N.',
            'applicationadshomebottomclickurl.url' => 'URL tujuan banner bawah halaman utama harus berupa URL yang valid.',
            'applicationadshomebottomclickurl.max' => 'URL tujuan banner bawah halaman utama maksimal 2048 karakter.',
            'applicationadslyric.image' => 'Banner iklan halaman lirik harus berupa file gambar.',
            'applicationadslyric.max' => 'Ukuran file banner iklan halaman lirik maksimal 2MB.',
            'applicationadslyrictype.in' => 'Jenis iklan halaman lirik tidak dikenali.',
            'applicationadslyricactive.in' => 'Status iklan halaman lirik harus bernilai Y atau N.',
            'applicationadslyricclickurl.url' => 'URL tujuan banner halaman lirik harus berupa URL yang valid.',
            'applicationadslyricclickurl.max' => 'URL tujuan banner halaman lirik maksimal 2048 karakter.',
            'applicationadslyrictop.image' => 'Banner iklan atas halaman lirik harus berupa file gambar.',
            'applicationadslyrictop.max' => 'Ukuran file banner atas halaman lirik maksimal 2MB.',
            'applicationadslyrictoptype.in' => 'Jenis iklan atas halaman lirik tidak dikenali.',
            'applicationadslyrictopactive.in' => 'Status iklan atas halaman lirik harus bernilai Y atau N.',
            'applicationadslyrictopclickurl.url' => 'URL tujuan banner atas halaman lirik harus berupa URL yang valid.',
            'applicationadslyrictopclickurl.max' => 'URL tujuan banner atas halaman lirik maksimal 2048 karakter.',
            'applicationadslyricbottom.image' => 'Banner iklan bawah halaman lirik harus berupa file gambar.',
            'applicationadslyricbottom.max' => 'Ukuran file banner bawah halaman lirik maksimal 2MB.',
            'applicationadslyricbottomtype.in' => 'Jenis iklan bawah halaman lirik tidak dikenali.',
            'applicationadslyricbottomactive.in' => 'Status iklan bawah halaman lirik harus bernilai Y atau N.',
            'applicationadslyricbottomclickurl.url' => 'URL tujuan banner bawah halaman lirik harus berupa URL yang valid.',
            'applicationadslyricbottomclickurl.max' => 'URL tujuan banner bawah halaman lirik maksimal 2048 karakter.',
            'applicationadsmusiktop.image' => 'Banner iklan atas halaman musik harus berupa file gambar.',
            'applicationadsmusiktop.max' => 'Ukuran file banner atas halaman musik maksimal 2MB.',
            'applicationadsmusiktoptype.in' => 'Jenis iklan atas halaman musik tidak dikenali.',
            'applicationadsmusiktopactive.in' => 'Status iklan atas halaman musik harus bernilai Y atau N.',
            'applicationadsmusiktopclickurl.url' => 'URL tujuan banner atas halaman musik harus berupa URL yang valid.',
            'applicationadsmusiktopclickurl.max' => 'URL tujuan banner atas halaman musik maksimal 2048 karakter.',
            'applicationadsmusik.image' => 'Banner iklan pop-up halaman musik harus berupa file gambar.',
            'applicationadsmusik.max' => 'Ukuran file banner pop-up halaman musik maksimal 2MB.',
            'applicationadsmusiktype.in' => 'Jenis iklan pop-up halaman musik tidak dikenali.',
            'applicationadsmusikactive.in' => 'Status iklan pop-up halaman musik harus bernilai Y atau N.',
            'applicationadsmusikclickurl.url' => 'URL tujuan banner pop-up halaman musik harus berupa URL yang valid.',
            'applicationadsmusikclickurl.max' => 'URL tujuan banner pop-up halaman musik maksimal 2048 karakter.',
            'applicationadsmusikbottom.image' => 'Banner iklan bawah halaman musik harus berupa file gambar.',
            'applicationadsmusikbottom.max' => 'Ukuran file banner bawah halaman musik maksimal 2MB.',
            'applicationadsmusikbottomtype.in' => 'Jenis iklan bawah halaman musik tidak dikenali.',
            'applicationadsmusikbottomactive.in' => 'Status iklan bawah halaman musik harus bernilai Y atau N.',
            'applicationadsmusikbottomclickurl.url' => 'URL tujuan banner bawah halaman musik harus berupa URL yang valid.',
            'applicationadsmusikbottomclickurl.max' => 'URL tujuan banner bawah halaman musik maksimal 2048 karakter.',
            'applicationadsbottom.image' => 'Banner iklan bawah harus berupa file gambar.',
            'applicationadsbottom.max' => 'Ukuran file banner iklan bawah maksimal 2MB.',
            'applicationadsbottomtype.in' => 'Jenis iklan bawah tidak dikenali.',
            'applicationadsbottomactive.required' => 'Status iklan bawah wajib dipilih.',
            'applicationadsbottomactive.in' => 'Status iklan bawah harus bernilai Y atau N.',
            'applicationadsbottomclickurl.url' => 'URL tujuan banner bawah harus berupa URL yang valid.',
            'applicationadsbottomclickurl.max' => 'URL tujuan banner bawah maksimal 2048 karakter.',
            'category_ads.*.*.image.image' => 'Banner iklan kategori harus berupa file gambar.',
            'category_ads.*.*.image.max' => 'Ukuran file banner iklan kategori maksimal 2MB.',
            'category_ads.*.*.type.in' => 'Jenis iklan kategori tidak dikenali.',
            'category_ads.*.*.active.required' => 'Status iklan kategori wajib dipilih.',
            'category_ads.*.*.active.in' => 'Status iklan kategori harus bernilai Y atau N.',
            'category_ads.*.*.clickurl.url' => 'URL tujuan banner kategori harus berupa URL yang valid.',
            'category_ads.*.*.clickurl.max' => 'URL tujuan banner kategori maksimal 2048 karakter.',
        ]);

        $setting = Setting::first();

        // Normalisasi nilai default jenis iklan bila tidak dikirim oleh form.
        foreach (['applicationads1type', 'applicationads2type', 'applicationadshometype', 'applicationadshometoptype', 'applicationadshomebottomtype', 'applicationadslyrictype', 'applicationadslyrictoptype', 'applicationadslyricbottomtype', 'applicationadsmusiktoptype', 'applicationadsmusiktype', 'applicationadsmusikbottomtype', 'applicationadsbottomtype'] as $typeField) {
            if (empty($validated[$typeField])) {
                $validated[$typeField] = $setting?->$typeField ?? 'image';
            }
        }

        $adFields = ['applicationads1', 'applicationads2', 'applicationadshome', 'applicationadshometop', 'applicationadshomebottom', 'applicationadslyric', 'applicationadslyrictop', 'applicationadslyricbottom', 'applicationadsmusiktop', 'applicationadsmusik', 'applicationadsmusikbottom', 'applicationadsbottom'];
        foreach ($adFields as $field) {
            if ($request->hasFile($field)) {
                if ($setting && $setting->$field && ! filter_var($setting->$field, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($setting->$field)) {
                    Storage::disk('public')->delete($setting->$field);
                }
                $validated[$field] = $request->file($field)->store('banners', 'public');

                // Berkas gambar yang diunggah menang atas pilihan jenis lain:
                // pastikan slot memakai format gambar dan buang Ad Unit lama.
                $validated[$field.'type'] = 'image';
                $validated[$field.'unit'] = null;
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

        $categoryAds = $validated['category_ads'] ?? [];
        unset($validated['category_ads']);

        $this->updateCategoryAds($request, $categoryAds);

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

    /**
     * Simpan konfigurasi slot iklan per kategori (atas, pop-up, dan bawah).
     *
     * @param  array<int|string, array<string, array<string, mixed>>>  $categoryAds
     */
    private function updateCategoryAds(Request $request, array $categoryAds): void
    {
        foreach ($categoryAds as $categoryId => $slots) {
            $categoryId = (int) $categoryId;

            if (! is_array($slots) || ! Category::whereKey($categoryId)->exists()) {
                continue;
            }

            foreach ($slots as $slot => $data) {
                if (! in_array($slot, CategoryAd::SLOTS, true) || ! is_array($data)) {
                    continue;
                }

                $ad = CategoryAd::firstOrNew([
                    'songcategoryid' => $categoryId,
                    'slot' => $slot,
                ]);

                $fileKey = "category_ads.{$categoryId}.{$slot}.image";

                if ($request->hasFile($fileKey)) {
                    // Berkas gambar yang diunggah menang atas pilihan jenis lain.
                    $ad->deleteStoredImage();
                    $ad->image = $request->file($fileKey)->store('banners', 'public');
                    $ad->type = 'image';
                    $ad->unit = null;
                } else {
                    if ($request->boolean("category_ads.{$categoryId}.{$slot}.delete_image")) {
                        $ad->deleteStoredImage();
                        $ad->image = null;
                        $ad->clickurl = null;
                    }

                    $ad->type = $data['type'] ?? $ad->type ?? 'image';
                    $ad->unit = $data['unit'] ?? $ad->unit;
                }

                $ad->active = $data['active'] ?? $ad->active ?? 'Y';
                $ad->clickurl = $data['clickurl'] ?? $ad->clickurl;
                $ad->save();
            }
        }
    }
}
