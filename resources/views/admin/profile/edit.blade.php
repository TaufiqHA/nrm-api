@extends('layouts.admin')

@section('page-title', 'Profil Saya')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-bold text-white tracking-tight">Profil Saya</h1>
    </div>

    <!-- Alert Notifications -->
    @if (session('success_profile'))
        <riv class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-400 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>{{ session('success_profile') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if (session('success_password'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-400 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>{{ session('success_password') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-300">
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

    <!-- Profile Sections (Informasi Pribadi & Kontak dan Keamanan & Kata Sandi) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full items-start">

        <!-- Card 1: Informasi Pribadi & Kontak -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-800 pb-4">
                <h2 class="text-base font-semibold text-white">Informasi Pribadi &amp; Kontak</h2>
                <p class="text-xs text-slate-400 mt-1">Perbarui nama lengkap, username, dan alamat email akun Anda</p>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="profile_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Nama Lengkap <span class="text-rose-400">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="profile_name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="profile_username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Username <span class="text-rose-400">*</span>
                        </label>
                        <input type="text"
                               name="username"
                               id="profile_username"
                               value="{{ old('username', $user->username) }}"
                               required
                               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>

                    <div>
                        <label for="profile_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Alamat Email <span class="text-rose-400">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               id="profile_email"
                               value="{{ old('email', $user->email) }}"
                               required
                               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Keamanan & Kata Sandi -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-800 pb-4">
                <h2 class="text-base font-semibold text-white">Keamanan &amp; Kata Sandi</h2>
                <p class="text-xs text-slate-400 mt-1">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak demi keamanan</p>
            </div>

            <form method="POST" action="{{ route('admin.profile.password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Password Saat Ini <span class="text-rose-400">*</span>
                    </label>
                    <input type="password"
                           name="current_password"
                           id="current_password"
                           required
                           placeholder="Masukkan password Anda saat ini"
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Password Baru <span class="text-rose-400">*</span>
                        </label>
                        <input type="password"
                               name="password"
                               id="new_password"
                               required
                               minlength="8"
                               placeholder="Min. 8 karakter"
                               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Ulangi Password Baru <span class="text-rose-400">*</span>
                        </label>
                        <input type="password"
                               name="password_confirmation"
                               id="new_password_confirmation"
                               required
                               minlength="8"
                               placeholder="Ulangi password baru"
                               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
