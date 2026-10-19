@php
    // Partial satu slot iklan: jenis iklan (gambar/AdMob), Ad Unit ID, dan
    // unggahan berkas gambar. Variabel yang diharapkan:
    //   $setting : App\Models\Setting|null
    //   $name    : nama field gambar, mis. "applicationads1"
    //   $label   : judul slot, mis. "Slot 1 — Header / Atas"
    $name = $name ?? 'applicationads1';
    $label = $label ?? 'Slot Iklan';

    $typeName = $name.'type';       // applicationads1type
    $unitName = $name.'unit';       // applicationads1unit
    $activeName = $name.'active';   // applicationads1active
    $clickUrlName = $name.'clickurl'; // applicationads1clickurl
    $deleteName = 'delete_'.$name;  // delete_applicationads1

    $typeValue = old($typeName, $setting->$typeName ?? 'image');
    $unitValue = old($unitName, $setting->$unitName ?? '');
    $activeValue = old($activeName, $setting->$activeName ?? 'Y');
    $clickUrlValue = old($clickUrlName, $setting->$clickUrlName ?? '');
    $imageValue = $setting->$name ?? null;
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
        <label for="{{ $activeName }}" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
            Status Iklan
        </label>
        <select name="{{ $activeName }}"
                id="{{ $activeName }}"
                required
                class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            <option value="Y" {{ $activeValue === 'Y' ? 'selected' : '' }}>Aktif (Ditampilkan)</option>
            <option value="N" {{ $activeValue === 'N' ? 'selected' : '' }}>Nonaktif (Disembunyikan)</option>
        </select>
        @error($activeName)
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Jenis iklan --}}
    <div>
        <label for="{{ $typeName }}" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
            Jenis Iklan
        </label>
        <select name="{{ $typeName }}"
                id="{{ $typeName }}"
                onchange="toggleAdType(this)"
                class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            @foreach ($adTypes as $value => $text)
                <option value="{{ $value }}" {{ $typeValue === $value ? 'selected' : '' }}>{{ $text }}</option>
            @endforeach
        </select>
        @error($typeName)
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Ad Unit ID (tampil bila jenis AdMob) --}}
    <div data-ad-unit class="{{ $isImage ? 'hidden' : '' }}">
        <label for="{{ $unitName }}" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
            Ad Unit ID AdMob
        </label>
        <input type="text"
               name="{{ $unitName }}"
               id="{{ $unitName }}"
               value="{{ $unitValue }}"
               placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy"
               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
        <p class="text-[11px] text-slate-400 mt-1">Wajib diisi bila jenis iklan AdMob. Ambil dari dashboard AdMob (ad unit).</p>
        @error($unitName)
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
                        <input type="checkbox" name="{{ $deleteName }}" value="1" class="rounded border-slate-800 bg-slate-950 text-rose-500 focus:ring-0">
                        <span>Hapus banner ini</span>
                    </label>
                </div>
            </div>
        @endif

        <input type="file"
               name="{{ $name }}"
               id="{{ $name }}"
               accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
               class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer rounded-xl border border-slate-800 bg-slate-950 p-1.5 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors cursor-pointer">
        <p class="text-[11px] text-slate-400 mt-1">JPG, PNG, WEBP, GIF, atau SVG (Maks. 2MB). Kosongkan jika tidak ingin mengubah.</p>
        @error($name)
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror

        {{-- URL tujuan saat banner diklik --}}
        <div class="mt-3">
            <label for="{{ $clickUrlName }}" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
                URL Tujuan Saat Gambar Diklik
            </label>
            <input type="url"
                   name="{{ $clickUrlName }}"
                   id="{{ $clickUrlName }}"
                   value="{{ $clickUrlValue }}"
                   placeholder="https://contoh.com/promo"
                   class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            <p class="text-[11px] text-slate-400 mt-1">Opsional. Jika diisi, gambar akan membuka tautan ini saat diklik.</p>
            @error($clickUrlName)
                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
