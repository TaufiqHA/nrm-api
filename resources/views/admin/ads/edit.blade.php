@extends('layouts.admin')

@section('page-title', 'Iklan')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="pb-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Konfigurasi Iklan</h1>
            <p class="text-sm text-slate-300 mt-1">Kelola banner gambar dan Ad Unit AdMob untuk slot halaman musik (atas, konten, dan bawah), halaman BUKU MERAH (Musik), halaman utama, serta slot khusus halaman lirik.</p>
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
    <form method="POST" action="{{ route('admin.ads.update') }}" enctype="multipart/form-data" class="space-y-6 w-full">
        @csrf
        @method('PUT')

        <div class="space-y-6 w-full">

            <!-- Halaman Musik -->
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
                <div class="flex flex-col gap-3 border-b border-slate-800 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-800 bg-slate-950 text-blue-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.633 2.163l-1.319.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white">Iklan Halaman BUKU MERAH (Musik &amp; Lirik)</h2>
                            <p class="text-xs text-slate-400 mt-1">Slot header, pop-up, dan footer yang tampil pada halaman musik.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-slate-800 bg-slate-950 px-3 py-1 text-[11px] font-medium text-slate-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-400"></span>
                        3 slot
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <!-- Banner Slot 1 -->
                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationads1',
                        'label' => 'Slot 1 — Header / Atas',
                    ])

                    <!-- Banner Slot 2 -->
                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationads2',
                        'label' => 'Slot 2 — Konten / Pop-up',
                    ])

                    <!-- Iklan Bawah -->
                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadsbottom',
                        'label' => 'Iklan Bawah — Footer',
                    ])
                </div>
            </section>

            <!-- Halaman Lirik -->
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
                <div class="flex flex-col gap-3 border-b border-slate-800 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-800 bg-slate-950 text-emerald-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white">Iklan Halaman BUKU MERAH (Lirik)</h2>
                            <p class="text-xs text-slate-400 mt-1">Slot khusus mode Hanya Lirik, terpisah dari pengaturan halaman musik.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[11px] font-medium text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        3 slot terpisah
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadslyrictop',
                        'label' => 'Halaman Lirik — Atas',
                    ])

                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadslyric',
                        'label' => 'Slot 4 — Pop-up Halaman Lirik Only',
                    ])

                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadslyricbottom',
                        'label' => 'Halaman Lirik — Bawah',
                    ])
                </div>
            </section>

            <!-- Halaman BUKU MERAH (Musik) -->
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
                <div class="flex flex-col gap-3 border-b border-slate-800 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-800 bg-slate-950 text-violet-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm0 0v10.5m0 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm0 0 9-3.75M18 18.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm0 0V6.75m0 0a3 3 0 1 0 6 0 3 3 0 0 0-6 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white">Iklan Halaman BUKU MERAH (Musik)</h2>
                            <p class="text-xs text-slate-400 mt-1">Slot khusus halaman pemutar musik, terpisah dari halaman karaoke dan halaman lirik.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-violet-500/20 bg-violet-500/10 px-3 py-1 text-[11px] font-medium text-violet-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-violet-400"></span>
                        3 slot terpisah
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadsmusiktop',
                        'label' => 'Halaman Musik — Atas',
                    ])

                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadsmusik',
                        'label' => 'Halaman Musik — Pop-up',
                    ])

                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadsmusikbottom',
                        'label' => 'Halaman Musik — Bawah',
                    ])
                </div>
            </section>

            <!-- Halaman Utama -->
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
                <div class="flex flex-col gap-3 border-b border-slate-800 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-800 bg-slate-950 text-amber-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white">Iklan Halaman Utama</h2>
                            <p class="text-xs text-slate-400 mt-1">Pop-up iklan yang tampil saat aplikasi pertama kali dibuka.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-slate-800 bg-slate-950 px-3 py-1 text-[11px] font-medium text-slate-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        1 slot
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadshome',
                        'label' => 'Slot 3 — Halaman Utama',
                    ])

                    <div class="flex h-full flex-col justify-center rounded-xl border border-dashed border-slate-800 bg-slate-950/40 p-6">
                        <div class="flex items-center gap-2 text-slate-200">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            <h3 class="text-sm font-semibold">Ringkasan Slot</h3>
                        </div>

                        <ul class="mt-4 space-y-3 text-sm text-slate-400">
                            <li class="flex gap-2.5">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400"></span>
                                <span><strong class="font-semibold text-slate-200">Halaman Musik:</strong> banner header, pop-up konten, dan footer.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-violet-400"></span>
                                <span><strong class="font-semibold text-slate-200">Halaman BUKU MERAH (Musik):</strong> banner atas, pop-up, dan banner bawah.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></span>
                                <span><strong class="font-semibold text-slate-200">Halaman Lirik:</strong> banner atas, pop-up, dan banner bawah dengan pengaturan sendiri.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></span>
                                <span><strong class="font-semibold text-slate-200">Halaman Utama:</strong> pop-up pembuka aplikasi.</span>
                            </li>
                        </ul>

                        <p class="mt-5 rounded-xl border border-blue-500/20 bg-blue-500/10 px-3.5 py-2.5 text-xs leading-relaxed text-blue-300">
                            Slot halaman musik, halaman BUKU MERAH (Musik), dan halaman lirik dikelola terpisah, sehingga banner tiap halaman tidak saling menimpa.
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Actions Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition-colors hover:bg-blue-500 active:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900 cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>Simpan Iklan</span>
            </button>
        </div>
    </form>

</div>

<script>
    // Tampilkan kolom Ad Unit ID hanya saat jenis iklan AdMob, dan kolom unggah
    // berkas gambar hanya saat jenis iklan = gambar.
    function toggleAdType(select) {
        var slot = select.closest('[data-ad-slot]');
        if (!slot) return;
        var unitBox = slot.querySelector('[data-ad-unit]');
        var imageBox = slot.querySelector('[data-ad-image]');
        var isImage = select.value === 'image';
        if (unitBox) unitBox.classList.toggle('hidden', isImage);
        if (imageBox) imageBox.classList.toggle('hidden', !isImage);
    }

    // Selaraskan tampilan saat halaman dimuat (mis. setelah validasi gagal).
    document.querySelectorAll('[data-ad-slot] select[name$="type"]').forEach(function (select) {
        toggleAdType(select);
    });
</script>
@endsection
