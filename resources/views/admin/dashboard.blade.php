@extends('layouts.admin')

@section('page-title', 'Overview Dashboard')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Overview Dashboard</h1>
            <p class="text-sm text-slate-300 mt-1">Selamat Datang, {{ auth()->user()->name }}</p>
        </div>
        <div class="text-sm font-mono text-slate-400">
            {{ now()->format('d M Y') }}
        </div>
    </div>

    <!-- 2 Essential Metric Cards (Full Width Grid) -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <!-- Lagu -->
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-300">Total Koleksi Lagu</span>
                <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.633 2.163l-1.319.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                </svg>
            </div>
            <div class="mt-4">
                <span class="text-3xl sm:text-4xl font-bold font-mono text-white">{{ $stats['total_songs'] }}</span>
            </div>
        </div>

        <!-- Kategori -->
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-300">Kategori / Genre</span>
                <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
            </div>
            <div class="mt-4">
                <span class="text-3xl sm:text-4xl font-bold font-mono text-white">{{ $stats['total_categories'] }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
