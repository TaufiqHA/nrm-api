@php
    // Partial satu slot iklan kategori. Variabel yang diharapkan:
    //   $ad         : App\Models\CategoryAd|null
    //   $categoryId : int
    //   $slot       : string (top/popup/bottom)
    //   $label      : judul slot
    $label = $label ?? 'Slot Iklan Kategori';
    $base = "category_ads.{$categoryId}.{$slot}";

    $typeValue = old("{$base}.type", $ad->type ?? 'image');
    $unitValue = old("{$base}.unit", $ad->unit ?? '');
    $activeValue = old("{$base}.active", $ad->active ?? 'Y');
    $clickUrlValue = old("{$base}.clickurl", $ad->clickurl ?? '');
    $imageValue = $ad->image ?? null;
    $isImage = ($typeValue === null || $typeValue === '' || $typeValue === 'image');

    $adTypes = [
        'image' => 'Banner Gambar (unggah berkas)',
        'admob_native' => 'AdMob Native (dalam konten)',
        'admob_banner' => 'AdMob Banner (dalam konten)',
        'admob_interstitial' => 'AdMob Interstitial (layar penuh)',
        'admob_app_open' => 'AdMob App Open (layar penuh)',
        'admob_reward' => 'AdMob Reward (layar penuh)',
    ];
@endphp

<div class="space-y-3 rounded-xl border border-slate-800/60 bg-slate-950/40 p-3" data-ad-slot>
    <p class="text-xs font-semibold uppercase tracking-wider text-slate-200">{{ $label }}</p>

    {{-- Status iklan per slot --}}
    <div>
        <label for="{{ $base }}.active" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
            Status Iklan
        </label>
        <select name="category_ads[{{ $categoryId }}][{{ $slot }}][active]"
                id="{{ $base }}.active"
                required
                class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            <option value="Y" {{ $activeValue === 'Y' ? 'selected' : '' }}>Aktif (Ditampilkan)</option>
            <option value="N" {{ $activeValue === 'N' ? 'selected' : '' }}>Nonaktif (Disembunyikan)</option>
        </select>
        @error("{$base}.active")
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Jenis iklan --}}
    <div>
        <label for="{{ $base }}.type" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
            Jenis Iklan
        </label>
        <select name="category_ads[{{ $categoryId }}][{{ $slot }}][type]"
                id="{{ $base }}.type"
                onchange="toggleAdType(this)"
                class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            @foreach ($adTypes as $value => $text)
                <option value="{{ $value }}" {{ $typeValue === $value ? 'selected' : '' }}>{{ $text }}</option>
            @endforeach
        </select>
        @error("{$base}.type")
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Ad Unit ID (tampil bila jenis AdMob) --}}
    <div data-ad-unit class="{{ $isImage ? 'hidden' : '' }}">
        <label for="{{ $base }}.unit" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
            Ad Unit ID AdMob
        </label>
        <input type="text"
               name="category_ads[{{ $categoryId }}][{{ $slot }}][unit]"
               id="{{ $base }}.unit"
               value="{{ $unitValue }}"
               placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy"
               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
        <p class="text-[11px] text-slate-400 mt-1">Opsional. Boleh dikosongkan bila belum memiliki Ad Unit ID.</p>
        @error("{$base}.unit")
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Unggahan berkas gambar (tampil bila jenis = gambar) --}}
    <div data-ad-image class="{{ $isImage ? '' : 'hidden' }}">
        @if ($imageValue)
            <div class="flex items-center gap-3 p-2.5 mb-2 rounded-xl border border-slate-800 bg-slate-950">
                <img src="{{ filter_var($imageValue, FILTER_VALIDATE_URL) ? $imageValue : asset('storage/' . $imageValue) }}"
                     alt="{{ $label }}"
                     class="h-12 w-20 object-cover rounded-lg border border-slate-800 bg-slate-900 shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-slate-200 truncate">{{ basename($imageValue) }}</p>
                    <label class="mt-1 inline-flex items-center gap-1.5 text-xs text-rose-400 hover:text-rose-300 cursor-pointer">
                        <input type="checkbox" name="category_ads[{{ $categoryId }}][{{ $slot }}][delete_image]" value="1" class="rounded border-slate-800 bg-slate-950 text-rose-500 focus:ring-0">
                        <span>Hapus banner ini</span>
                    </label>
                </div>
            </div>
        @endif

        <input type="file"
               name="category_ads[{{ $categoryId }}][{{ $slot }}][image]"
               id="{{ $base }}.image"
               accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
               class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer rounded-xl border border-slate-800 bg-slate-950 p-1.5 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors cursor-pointer">
        <p class="text-[11px] text-slate-400 mt-1">JPG, PNG, WEBP, GIF, atau SVG (Maks. 2MB). Kosongkan jika tidak ingin mengubah.</p>
        @error("{$base}.image")
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror

        {{-- URL tujuan saat banner diklik --}}
        <div class="mt-3">
            <label for="{{ $base }}.clickurl" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
                URL Tujuan Saat Gambar Diklik
            </label>
            <input type="url"
                   name="category_ads[{{ $categoryId }}][{{ $slot }}][clickurl]"
                   id="{{ $base }}.clickurl"
                   value="{{ $clickUrlValue }}"
                   placeholder="https://contoh.com/promo"
                   class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            <p class="text-[11px] text-slate-400 mt-1">Opsional. Gambar akan membuka tautan ini saat diklik.</p>
            @error("{$base}.clickurl")
                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
