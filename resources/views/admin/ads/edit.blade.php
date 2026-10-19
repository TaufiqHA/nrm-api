@extends('layouts.admin')

@section('page-title', 'Iklan')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="pb-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Konfigurasi Iklan</h1>
            <p class="text-sm text-slate-300 mt-1">Kelola banner gambar dan Ad Unit AdMob untuk slot atas, konten, serta banner bawah aplikasi.</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full items-start">

            <!-- Card 1: Iklan Utama (Slot 1 & Slot 2) -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-base font-semibold text-white">Iklan Utama</h2>
                    <p class="text-xs text-slate-400 mt-1">Banner/gambar atau AdMob untuk slot atas (header) dan slot konten (pop-up).</p>
                </div>

                <div class="space-y-5">
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
                </div>
            </div>

            <!-- Card 2: Iklan Bawah (Footer) -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-base font-semibold text-white">Iklan Bawah</h2>
                    <p class="text-xs text-slate-400 mt-1">Banner/gambar atau AdMob untuk bilah bawah (footer) aplikasi.</p>
                </div>

                <div class="space-y-4">
                    @include('admin.ads._ad_slot', [
                        'setting' => $setting,
                        'name' => 'applicationadsbottom',
                        'label' => 'Iklan Bawah — Footer',
                    ])
                </div>
            </div>
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
