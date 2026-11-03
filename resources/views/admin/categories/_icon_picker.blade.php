@php
    $iconOptions = config('category_icons');
    $pickerId = $prefix;
@endphp

<div class="space-y-4" data-icon-picker data-prefix="{{ $pickerId }}">
    <div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Sumber Ikon</span>
        <div class="grid grid-cols-3 gap-2">
            @foreach (['auto' => 'Otomatis', 'builtin' => 'Icon Bawaan', 'image' => 'Upload Gambar'] as $value => $label)
                <button type="button"
                        data-icon-source="{{ $value }}"
                        onclick="setIconSource('{{ $pickerId }}', '{{ $value }}')"
                        class="rounded-lg border px-2 py-2 text-xs font-medium transition-colors {{ $value === 'auto' ? 'border-blue-500 bg-blue-600/20 text-blue-300' : 'border-slate-800 bg-slate-950 text-slate-400 hover:text-slate-200' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <input type="hidden" name="icon_source" id="{{ $pickerId }}_icon_source" value="auto">
    </div>

    <div data-icon-section="auto" class="rounded-xl border border-slate-800 bg-slate-950 p-3 text-xs text-slate-400">
        Ikon akan ditentukan otomatis dari nama kategori (seperti perilaku sebelumnya).
    </div>

    <div data-icon-section="builtin" class="hidden space-y-2">
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Pilih Icon Bawaan</span>
        <input type="hidden" name="icon" id="{{ $pickerId }}_icon" value="">
        <div class="grid max-h-52 grid-cols-6 gap-2 overflow-y-auto rounded-xl border border-slate-800 bg-slate-950 p-2 sm:grid-cols-8">
            @foreach ($iconOptions as $key => $label)
                <button type="button"
                        data-icon-key="{{ $key }}"
                        title="{{ $label }}"
                        onclick="setIconKey('{{ $pickerId }}', '{{ $key }}')"
                        class="icon-key-btn flex aspect-square items-center justify-center rounded-lg border border-transparent text-slate-300 transition-colors hover:border-slate-700 hover:bg-slate-800">
                    <span class="material-symbols-rounded text-[22px]">{{ $key }}</span>
                </button>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-500" data-icon-key-label></p>
    </div>

    <div data-icon-section="image" class="hidden space-y-2">
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Unggah Gambar Icon</span>
        <input type="file"
               name="icon_file"
               id="{{ $pickerId }}_icon_file"
               accept="image/png,image/jpeg,image/webp"
               onchange="previewIconFile('{{ $pickerId }}')"
               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-white">
        <p class="text-[11px] text-slate-500">Format PNG, JPG, atau WEBP. Maksimal 1 MB. Disarankan gambar persegi dengan latar transparan.</p>
        <div data-icon-preview class="hidden items-center gap-3 rounded-xl border border-slate-800 bg-slate-950 p-3">
            <img src="" alt="Pratinjau ikon" class="h-10 w-10 rounded-lg bg-slate-900 object-contain p-1">
            <span class="text-xs text-slate-400">Pratinjau ikon</span>
        </div>
        <label data-icon-remove class="hidden items-center gap-2 text-xs text-rose-300">
            <input type="checkbox" name="remove_icon" value="1" class="rounded border-slate-700 bg-slate-950 text-rose-600">
            Hapus gambar ikon yang tersimpan
        </label>
    </div>
</div>
