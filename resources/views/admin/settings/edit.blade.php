@extends('layouts.admin')

@section('page-title', 'Pengaturan Aplikasi')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="pb-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Pengaturan Aplikasi</h1>
            <p class="text-sm text-slate-300 mt-1">Kelola informasi identitas aplikasi serta konfigurasi berkas banner dan penayangan iklan.</p>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-400 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-300" aria-label="Tutup notifikasi">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm font-medium text-rose-400 space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Form Section -->
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6 w-full">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full items-start">

            <!-- Card 1: Identitas & Informasi Aplikasi -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-base font-semibold text-white">Identitas &amp; Informasi Aplikasi</h2>
                    <p class="text-xs text-slate-400 mt-1">Konfigurasi nama perusahaan pemilik dan nama tampilan aplikasi karaoke</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="applicationcompany" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Nama Perusahaan <span class="text-rose-400">*</span>
                        </label>
                        <input type="text"
                               name="applicationcompany"
                               id="applicationcompany"
                               value="{{ old('applicationcompany', $setting->applicationcompany) }}"
                               required
                               maxlength="100"
                               placeholder="Contoh: PT Karaoke Nusantara"
                               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                        @error('applicationcompany')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="applicationname" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Nama Aplikasi <span class="text-rose-400">*</span>
                        </label>
                        <input type="text"
                               name="applicationname"
                               id="applicationname"
                               value="{{ old('applicationname', $setting->applicationname) }}"
                               required
                               maxlength="255"
                               placeholder="Contoh: Karaoke Pro App"
                               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                        @error('applicationname')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-800/80 bg-slate-950/60 p-4 text-xs text-slate-400 space-y-1.5">
                    <p class="font-medium text-slate-300 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        Informasi Penggunaan
                    </p>
                    <p>Nama aplikasi dan perusahaan akan digunakan di seluruh antarmuka sistem dan respon API aplikasi karaoke.</p>
                </div>
            </div>

            <!-- Card 2: Konfigurasi Iklan & Banner File Upload -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-base font-semibold text-white">Konfigurasi Iklan &amp; Banner</h2>
                    <p class="text-xs text-slate-400 mt-1">Unggah berkas gambar banner slot atas, slot 2, serta banner bawah</p>
                </div>

                <div class="space-y-5">
                    <!-- Banner Slot 1 -->
                    <div class="space-y-2">
                        <label for="applicationads1" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Berkas Banner Slot 1 (Header / Atas)
                        </label>
                        
                        @if ($setting->applicationads1)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-800 bg-slate-950">
                                <img src="{{ filter_var($setting->applicationads1, FILTER_VALIDATE_URL) ? $setting->applicationads1 : asset('storage/' . $setting->applicationads1) }}" 
                                     alt="Banner Slot 1" 
                                     class="h-12 w-20 object-cover rounded-lg border border-slate-800 bg-slate-900 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-200 truncate">{{ basename($setting->applicationads1) }}</p>
                                    <label class="mt-1 inline-flex items-center gap-1.5 text-xs text-rose-400 hover:text-rose-300 cursor-pointer">
                                        <input type="checkbox" name="delete_applicationads1" value="1" class="rounded border-slate-800 bg-slate-950 text-rose-500 focus:ring-0">
                                        <span>Hapus banner ini</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <input type="file"
                               name="applicationads1"
                               id="applicationads1"
                               accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                               class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer rounded-xl border border-slate-800 bg-slate-950 p-1.5 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors cursor-pointer">
                        <p class="text-[11px] text-slate-400">JPG, PNG, WEBP, GIF, atau SVG (Maks. 2MB). Kosongkan jika tidak ingin mengubah.</p>
                        @error('applicationads1')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Banner Slot 2 -->
                    <div class="space-y-2">
                        <label for="applicationads2" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Berkas Banner Slot 2 (Konten / Pop-up)
                        </label>

                        @if ($setting->applicationads2)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-800 bg-slate-950">
                                <img src="{{ filter_var($setting->applicationads2, FILTER_VALIDATE_URL) ? $setting->applicationads2 : asset('storage/' . $setting->applicationads2) }}" 
                                     alt="Banner Slot 2" 
                                     class="h-12 w-20 object-cover rounded-lg border border-slate-800 bg-slate-900 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-200 truncate">{{ basename($setting->applicationads2) }}</p>
                                    <label class="mt-1 inline-flex items-center gap-1.5 text-xs text-rose-400 hover:text-rose-300 cursor-pointer">
                                        <input type="checkbox" name="delete_applicationads2" value="1" class="rounded border-slate-800 bg-slate-950 text-rose-500 focus:ring-0">
                                        <span>Hapus banner ini</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <input type="file"
                               name="applicationads2"
                               id="applicationads2"
                               accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                               class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer rounded-xl border border-slate-800 bg-slate-950 p-1.5 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors cursor-pointer">
                        <p class="text-[11px] text-slate-400">JPG, PNG, WEBP, GIF, atau SVG (Maks. 2MB). Kosongkan jika tidak ingin mengubah.</p>
                        @error('applicationads2')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Banner Utama -->
                    <div>
                        <label for="applicationadsactive" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Status Iklan Utama <span class="text-rose-400">*</span>
                        </label>
                        <select name="applicationadsactive"
                                id="applicationadsactive"
                                required
                                class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                            <option value="Y" {{ old('applicationadsactive', $setting->applicationadsactive) === 'Y' ? 'selected' : '' }}>Aktif (Ditampilkan)</option>
                            <option value="N" {{ old('applicationadsactive', $setting->applicationadsactive) === 'N' ? 'selected' : '' }}>Nonaktif (Disembunyikan)</option>
                        </select>
                        @error('applicationadsactive')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Divider Banner Bawah -->
                    <div class="border-t border-slate-800/80 pt-4 space-y-4">
                        <div class="space-y-2">
                            <label for="applicationadsbottom" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                                Berkas Banner Iklan Bawah (Footer)
                            </label>

                            @if ($setting->applicationadsbottom)
                                <div class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-800 bg-slate-950">
                                    <img src="{{ filter_var($setting->applicationadsbottom, FILTER_VALIDATE_URL) ? $setting->applicationadsbottom : asset('storage/' . $setting->applicationadsbottom) }}" 
                                         alt="Banner Bawah" 
                                         class="h-12 w-20 object-cover rounded-lg border border-slate-800 bg-slate-900 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-slate-200 truncate">{{ basename($setting->applicationadsbottom) }}</p>
                                        <label class="mt-1 inline-flex items-center gap-1.5 text-xs text-rose-400 hover:text-rose-300 cursor-pointer">
                                            <input type="checkbox" name="delete_applicationadsbottom" value="1" class="rounded border-slate-800 bg-slate-950 text-rose-500 focus:ring-0">
                                            <span>Hapus banner ini</span>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <input type="file"
                                   name="applicationadsbottom"
                                   id="applicationadsbottom"
                                   accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                                   class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer rounded-xl border border-slate-800 bg-slate-950 p-1.5 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors cursor-pointer">
                            <p class="text-[11px] text-slate-400">JPG, PNG, WEBP, GIF, atau SVG (Maks. 2MB). Kosongkan jika tidak ingin mengubah.</p>
                            @error('applicationadsbottom')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="applicationadsbottomactive" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                                Status Iklan Bawah <span class="text-rose-400">*</span>
                            </label>
                            <select name="applicationadsbottomactive"
                                    id="applicationadsbottomactive"
                                    required
                                    class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                                <option value="Y" {{ old('applicationadsbottomactive', $setting->applicationadsbottomactive) === 'Y' ? 'selected' : '' }}>Aktif (Ditampilkan)</option>
                                <option value="N" {{ old('applicationadsbottomactive', $setting->applicationadsbottomactive) === 'N' ? 'selected' : '' }}>Nonaktif (Disembunyikan)</option>
                            </select>
                            @error('applicationadsbottomactive')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky or Bottom Actions Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition-colors hover:bg-blue-500 active:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900 cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>Simpan Pengaturan</span>
            </button>
        </div>
    </form>

</div>
@endsection
