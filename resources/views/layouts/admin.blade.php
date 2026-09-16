<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - Karaoke App</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased bg-slate-950 text-slate-100 flex selection:bg-blue-600 selection:text-white">

    <!-- Modular Sidebar (File Terpisah) -->
    @include('layouts.partials.sidebar')

    <!-- Main Content Area -->
    <div class="flex flex-1 flex-col min-w-0 min-h-screen">
        <!-- Topbar -->
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-800 bg-slate-900/90 px-5 sm:px-7 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <button type="button" 
                        onclick="toggleSidebar()" 
                        class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden"
                        aria-label="Toggle menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <h2 class="text-base font-semibold text-slate-200">@yield('page-title', 'Dashboard')</h2>
            </div>
        </header>

        <!-- Content (File Terpisah) -->
        <main class="flex-1 p-5 sm:p-7 lg:p-8 w-full">
            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            if (sidebar && backdrop) {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            }
        }
    </script>
</body>
</html>
