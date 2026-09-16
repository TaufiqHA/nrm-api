<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login - Karaoke App</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <!-- Brand / Header -->
        <div class="mb-6 text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white mb-3">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.633 2.163l-1.319.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-white">KaraokeApp Admin</h1>
        </div>

        <!-- Login Card -->
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-lg">
            <!-- Status Alert -->
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-3 text-xs text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Errors Alert -->
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-rose-500/10 border border-rose-500/20 p-3 text-xs text-rose-400 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <!-- Username / Email -->
                <div>
                    <label for="username" class="block text-xs font-medium text-slate-300 mb-1.5">
                        Username atau Email
                    </label>
                    <input type="text" 
                           name="username" 
                           id="username" 
                           value="{{ old('username') }}" 
                           required 
                           autofocus
                           class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">
                        Password
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required
                           class="block w-full rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <!-- Remember Me -->
                <div class="flex items-center pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember"
                               class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-blue-600 focus:ring-blue-500/20">
                        <span class="text-xs text-slate-300">Ingat saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-1">
                    <button type="submit" 
                            class="w-full rounded-lg bg-blue-600 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
