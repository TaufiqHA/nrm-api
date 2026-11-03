<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - NRM</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Material Symbols (pratinjau ikon kategori) -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <style>
        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: normal;
            font-style: normal;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased bg-slate-950 text-slate-100 flex selection:bg-blue-600 selection:text-white">

    <!-- Modular Sidebar (File Terpisah) -->
    @include('layouts.partials.sidebar')

    <!-- Main Content Area -->
    <div class="flex flex-1 flex-col min-w-0 min-h-screen">
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
