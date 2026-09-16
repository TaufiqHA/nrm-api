<!-- Sidebar Backdrop for Mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 z-40 bg-black/60 lg:hidden hidden" onclick="toggleSidebar()"></div>

<!-- Sidebar Container -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col justify-between border-r border-slate-800 bg-slate-900 p-5 transition-transform duration-200 ease-in-out -translate-x-full lg:static lg:translate-x-0">
    <!-- Top: Brand & Menu -->
    <div class="space-y-6">
        <!-- Brand -->
        <div class="flex items-center justify-between px-2 pt-1">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.633 2.163l-1.319.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                    </svg>
                </div>
                <span class="text-base font-bold text-white tracking-tight">KaraokeApp</span>
            </div>

            <!-- Close button for mobile -->
            <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden" onclick="toggleSidebar()" aria-label="Tutup sidebar">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-1.5">
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.songs.index') }}" 
               class="flex items-center justify-between rounded-xl px-3.5 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('admin.songs.*') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('admin.songs.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.633 2.163l-1.319.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                    </svg>
                    <span>Lagu</span>
                </div>
                <span class="text-xs font-mono {{ request()->routeIs('admin.songs.*') ? 'text-blue-200' : 'text-slate-400' }}">{{ $stats['total_songs'] ?? '' }}</span>
            </a>

            <a href="{{ route('admin.categories.index') }}" 
               class="flex items-center justify-between rounded-xl px-3.5 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('admin.categories.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                    <span>Kategori</span>
                </div>
                <span class="text-xs font-mono {{ request()->routeIs('admin.categories.*') ? 'text-blue-200' : 'text-slate-400' }}">{{ $stats['total_categories'] ?? '' }}</span>
            </a>

            <a href="{{ route('admin.nadas.index') }}" 
               class="flex items-center justify-between rounded-xl px-3.5 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('admin.nadas.*') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('admin.nadas.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.633 2.163l-1.319.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                    </svg>
                    <span>Nada</span>
                </div>
                <span class="text-xs font-mono {{ request()->routeIs('admin.nadas.*') ? 'text-blue-200' : 'text-slate-400' }}">{{ $stats['total_nadas'] ?? '' }}</span>
            </a>

            <a href="{{ route('admin.settings.edit') }}" 
               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-base font-medium transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                <svg class="h-5 w-5 {{ request()->routeIs('admin.settings.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span>Pengaturan</span>
            </a>
        </nav>
    </div>

    <!-- Bottom: User & Logout -->
    <div class="border-t border-slate-800 pt-4 space-y-2">
        @auth
        <a href="{{ route('admin.profile.edit') }}" 
           class="group flex items-center justify-between rounded-xl px-2.5 py-2 transition-colors {{ request()->routeIs('admin.profile.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/60' }}"
           title="Buka Profil Saya">
            <div class="truncate">
                <p class="truncate text-sm font-semibold text-white group-hover:text-blue-400 transition-colors">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-400 font-mono">@<span>{{ auth()->user()->username }}</span></p>
            </div>
            <svg class="h-4 w-4 text-slate-500 group-hover:text-blue-400 transition-colors shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </a>
        @endauth

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-rose-400 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span>Keluar (Logout)</span>
            </button>
        </form>
    </div>
</aside>
