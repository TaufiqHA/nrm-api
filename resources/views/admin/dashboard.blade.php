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

    <!-- 3 Essential Metric Cards (Full Width Grid) -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
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

        <!-- Pengguna -->
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-300">Total Pengguna</span>
                <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <div class="mt-4">
                <span class="text-3xl sm:text-4xl font-bold font-mono text-white">{{ $stats['total_users'] }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
