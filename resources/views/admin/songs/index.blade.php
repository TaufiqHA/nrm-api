@extends('layouts.admin')

@section('page-title', 'Katalog Lagu')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Katalog Lagu</h1>
            <p class="text-sm text-slate-300 mt-1">Kelola daftar katalog lagu karaoke dan file pemutar</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" 
                    onclick="openCreateNadaModal()" 
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-sm font-medium text-slate-200 hover:bg-slate-800 hover:text-white active:bg-slate-700 transition-colors shadow-sm">
                <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Nada</span>
            </button>

            <button type="button" 
                    onclick="openCreateModal()" 
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Lagu</span>
            </button>
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
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm font-medium text-rose-400 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-300">
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

    <!-- Search & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('admin.songs.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full max-w-2xl">
            <!-- Search Text -->
            <div class="relative w-full sm:flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari judul lagu atau pencipta..." 
                       class="block w-full h-12 rounded-xl border border-slate-800 bg-slate-900 pl-12 pr-4 text-base text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <!-- Category Filter -->
            <div class="w-full sm:w-44 shrink-0">
                <select name="category" 
                        onchange="this.form.submit()" 
                        class="block w-full h-12 rounded-xl border border-slate-800 bg-slate-900 px-3 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->songcategoryid }}" {{ request('category') == $cat->songcategoryid ? 'selected' : '' }}>
                            {{ $cat->songcategoryname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Button (on mobile/enter) -->
            <button type="submit" class="hidden">Cari</button>
        </form>

        @if(request('search') || request('category'))
            <a href="{{ route('admin.songs.index') }}" class="text-sm text-slate-400 hover:text-slate-200 underline">
                Reset Filter
            </a>
        @endif
    </div>

    <!-- Data Table Container -->
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/60 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-16">ID</th>
                        <th scope="col" class="px-6 py-4">Judul Lagu &amp; Pencipta</th>
                        <th scope="col" class="px-6 py-4">Kategori</th>
                        <th scope="col" class="px-6 py-4 text-center">Nada</th>
                        <th scope="col" class="px-6 py-4 text-center">Durasi</th>
                        <th scope="col" class="px-6 py-4">URL Lagu</th>
                        <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($songs as $song)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-mono text-slate-400">
                                #{{ $song->songid }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-white">{{ $song->songtitle }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $song->songsinger }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md bg-slate-800 px-2.5 py-1 text-xs font-medium text-blue-300 border border-slate-700">
                                    {{ $song->category->songcategoryname ?? 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-slate-300">
                                <span class="capitalize">{{ ucfirst($song->songnada ?? '—') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs text-slate-300">
                                {{ $song->songduration ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="truncate block max-w-xs font-mono text-xs text-slate-400" title="{{ $song->songurl }}">
                                    {{ $song->songurl }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            onclick='openEditModal(@json($song))'
                                            class="p-2 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-800 transition-colors"
                                            title="Edit Lagu">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            onclick="openDeleteModal({{ $song->songid }}, '{{ addslashes($song->songtitle) }}')"
                                            class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors"
                                            title="Hapus Lagu">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="h-8 w-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.633 2.163l-1.319.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                                    </svg>
                                    <p class="text-sm font-medium">Belum ada lagu dalam katalog</p>
                                    @if(request('search') || request('category'))
                                        <p class="text-xs text-slate-500">Tidak ada lagu yang cocok dengan kriteria pencarian saat ini</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($songs->hasPages())
            <div class="border-t border-slate-800 p-4">
                {{ $songs->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Create Song Modal -->
<div id="createModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-semibold text-white">Tambah Lagu Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.songs.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="create_songtitle" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Judul Lagu <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       name="songtitle" 
                       id="create_songtitle" 
                       required 
                       placeholder="Contoh: Menghapus Jejakmu"
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="create_songsinger" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Pencipta <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" 
                           name="songsinger" 
                           id="create_songsinger" 
                           required 
                           placeholder="Contoh: Ariel / Titiek Puspa"
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <div>
                    <label for="create_songcategory" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Kategori <span class="text-rose-400">*</span>
                    </label>
                    <select name="songcategory" 
                            id="create_songcategory" 
                            required 
                            class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->songcategoryid }}">{{ $cat->songcategoryname }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="create_songnada" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Nada
                    </label>
                    <select name="songnada" 
                            id="create_songnada" 
                            class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                        <option value="-">-</option>
                        @foreach ($nadas ?? [] as $nadaItem)
                            <option value="{{ $nadaItem->nada }}">{{ ucfirst($nadaItem->nada) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="create_songduration" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Durasi (MM:SS)
                    </label>
                    <input type="text" 
                           name="songduration" 
                           id="create_songduration" 
                           placeholder="Contoh: 04:12"
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
            </div>

            <div>
                <label for="create_songurl" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    URL Lagu <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       name="songurl" 
                       id="create_songurl" 
                       required 
                       placeholder="Contoh: https://storage.../lagu.mp3 atau audio/lagu.mp4"
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        onclick="closeCreateModal()" 
                        class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors">
                    Simpan Lagu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Song Modal -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-semibold text-white">Edit Lagu</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_songtitle" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Judul Lagu <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       name="songtitle" 
                       id="edit_songtitle" 
                       required 
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="edit_songsinger" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Pencipta <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" 
                           name="songsinger" 
                           id="edit_songsinger" 
                           required 
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <div>
                    <label for="edit_songcategory" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Kategori <span class="text-rose-400">*</span>
                    </label>
                    <select name="songcategory" 
                            id="edit_songcategory" 
                            required 
                            class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->songcategoryid }}">{{ $cat->songcategoryname }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="edit_songnada" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Nada
                    </label>
                    <select name="songnada" 
                            id="edit_songnada" 
                            class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                        <option value="-">-</option>
                        @foreach ($nadas ?? [] as $nadaItem)
                            <option value="{{ $nadaItem->nada }}">{{ ucfirst($nadaItem->nada) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit_songduration" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Durasi (MM:SS)
                    </label>
                    <input type="text" 
                           name="songduration" 
                           id="edit_songduration" 
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
            </div>

            <div>
                <label for="edit_songurl" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    URL Lagu <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       name="songurl" 
                       id="edit_songurl" 
                       required 
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        onclick="closeEditModal()" 
                        class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors">
                    Perbarui Lagu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-sm rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl space-y-4">
        <div class="flex items-center gap-3 text-rose-400">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 border border-rose-500/20">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-white">Hapus Lagu</h3>
                <p class="text-xs text-slate-400">Lagu akan dihapus dari database katalog</p>
            </div>
        </div>

        <p class="text-sm text-slate-300">
            Apakah Anda yakin ingin menghapus lagu <strong id="deleteSongTitle" class="text-white"></strong>?
        </p>

        <form id="deleteForm" method="POST" action="" class="flex items-center justify-end gap-3 pt-2">
            @csrf
            @method('DELETE')
            <button type="button" 
                    onclick="closeDeleteModal()" 
                    class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                Batal
            </button>
            <button type="submit" 
                    class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-rose-500 active:bg-rose-700 transition-colors">
                Hapus Lagu
            </button>
        </form>
    </div>
</div>

<!-- Modalbox Kelola & Tambah Nada -->
<div id="createNadaModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden" onclick="closeCreateNadaModal()">
    <div class="relative w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-2xl space-y-4" onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-white tracking-tight">Kelola Nada Lagu</h3>
                <p class="text-xs text-slate-400 mt-0.5">Tambahkan, edit, atau hapus pilihan nada vokal</p>
            </div>
            <button type="button" 
                    onclick="closeCreateNadaModal()" 
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white transition-colors"
                    aria-label="Tutup modal">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Success & Error Alert Boxes -->
        <div id="createNadaSuccess" class="hidden rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3 text-xs font-medium text-emerald-400 flex items-center justify-between">
            <span id="createNadaSuccessText"></span>
            <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-emerald-400 hover:text-emerald-300">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="createNadaError" class="hidden rounded-xl border border-rose-500/20 bg-rose-500/10 p-3 text-xs font-medium text-rose-400 flex items-center justify-between">
            <span id="createNadaErrorText"></span>
            <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-rose-400 hover:text-rose-300">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Tambah Nada Baru -->
        <form id="createNadaForm" method="POST" action="{{ route('admin.nadas.store') }}" class="space-y-1.5">
            @csrf
            <label for="create_nada_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                Tambah Nada Baru
            </label>
            <div class="flex items-center gap-2">
                <input type="text" 
                       name="nada" 
                       id="create_nada_name" 
                       required 
                       placeholder="Contoh: pria, wanita, duet, anak..."
                       class="block flex-1 rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                <button type="submit" 
                        id="createNadaSubmitBtn"
                        class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah</span>
                </button>
            </div>
        </form>

        <!-- Daftar Nada yang Sudah Ada -->
        <div class="space-y-2 pt-2 border-t border-slate-800">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-slate-300">
                <span>Daftar Nada Terdaftar</span>
                <span id="nadaCountBadge" class="text-slate-400 font-mono text-[11px] lowercase">{{ count($nadas ?? []) }} nada</span>
            </div>

            <div class="max-h-56 overflow-y-auto rounded-xl border border-slate-800 bg-slate-950/60 p-1.5">
                <ul id="nadaListContainer" class="space-y-1 divide-y divide-slate-800/40">
                    @forelse ($nadas ?? [] as $nadaItem)
                        <li id="nada-row-{{ $nadaItem->id }}" class="pt-1 first:pt-0">
                            <!-- View Mode -->
                            <div id="nada-view-{{ $nadaItem->id }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-800/40 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <span class="h-2 w-2 rounded-full bg-blue-500 shrink-0"></span>
                                    <span id="nada-text-{{ $nadaItem->id }}" class="text-sm font-medium text-white capitalize">{{ $nadaItem->nada }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" 
                                            onclick="startEditNada({{ $nadaItem->id }}, '{{ addslashes($nadaItem->nada) }}')" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-800 transition-colors" 
                                            title="Edit Nada">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            onclick="confirmDeleteNada({{ $nadaItem->id }}, '{{ addslashes($nadaItem->nada) }}')" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors" 
                                            title="Hapus Nada">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Inline Edit Mode -->
                            <div id="nada-edit-{{ $nadaItem->id }}" class="hidden flex items-center gap-2 p-1.5 bg-slate-900/90 rounded-lg border border-slate-700/60">
                                <input type="text" 
                                       id="nada-input-{{ $nadaItem->id }}" 
                                       value="{{ $nadaItem->nada }}" 
                                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); saveEditNada({{ $nadaItem->id }}); } if(event.key === 'Escape'){ event.preventDefault(); cancelEditNada({{ $nadaItem->id }}); }"
                                       class="flex-1 rounded-lg border border-slate-700 bg-slate-950 px-2.5 py-1.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500">
                                <button type="button" 
                                        id="nada-save-btn-{{ $nadaItem->id }}"
                                        onclick="saveEditNada({{ $nadaItem->id }})" 
                                        class="p-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white transition-colors" 
                                        title="Simpan Perubahan">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </button>
                                <button type="button" 
                                        onclick="cancelEditNada({{ $nadaItem->id }})" 
                                        class="p-1.5 rounded-lg border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" 
                                        title="Batal">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </li>
                    @empty
                        <li id="nadaEmptyState" class="text-center py-6 text-xs text-slate-400">
                            Belum ada nada yang tersimpan.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end pt-3 border-t border-slate-800">
            <button type="button" 
                    onclick="closeCreateNadaModal()" 
                    class="rounded-xl border border-slate-800 bg-slate-900 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                Tutup
            </button>
        </div>

    </div>
</div>

<script>
    function showNadaError(message) {
        const successBox = document.getElementById('createNadaSuccess');
        if (successBox) successBox.classList.add('hidden');

        const errorBox = document.getElementById('createNadaError');
        const errorText = document.getElementById('createNadaErrorText');
        if (errorBox && errorText) {
            errorText.textContent = message;
            errorBox.classList.remove('hidden');
        }
    }

    function showNadaSuccess(message) {
        const errorBox = document.getElementById('createNadaError');
        if (errorBox) errorBox.classList.add('hidden');

        const successBox = document.getElementById('createNadaSuccess');
        const successText = document.getElementById('createNadaSuccessText');
        if (successBox && successText) {
            successText.textContent = message;
            successBox.classList.remove('hidden');
        }
    }

    function clearNadaAlerts() {
        const errorBox = document.getElementById('createNadaError');
        if (errorBox) errorBox.classList.add('hidden');
        const successBox = document.getElementById('createNadaSuccess');
        if (successBox) successBox.classList.add('hidden');
    }

    function updateNadaCountBadge() {
        const listContainer = document.getElementById('nadaListContainer');
        const badge = document.getElementById('nadaCountBadge');
        if (listContainer && badge) {
            const count = listContainer.querySelectorAll('li[id^="nada-row-"]').length;
            badge.textContent = `${count} nada`;
        }
    }

    function openCreateNadaModal() {
        clearNadaAlerts();
        document.getElementById('createNadaModal').classList.remove('hidden');
        document.getElementById('create_nada_name').focus();
    }

    function closeCreateNadaModal() {
        document.getElementById('createNadaModal').classList.add('hidden');
    }

    function startEditNada(id, currentName) {
        clearNadaAlerts();
        const viewEl = document.getElementById(`nada-view-${id}`);
        const editEl = document.getElementById(`nada-edit-${id}`);
        const inputEl = document.getElementById(`nada-input-${id}`);

        if (viewEl && editEl && inputEl) {
            viewEl.classList.add('hidden');
            editEl.classList.remove('hidden');
            inputEl.value = currentName;
            inputEl.focus();
            inputEl.select();
        }
    }

    function cancelEditNada(id) {
        const viewEl = document.getElementById(`nada-view-${id}`);
        const editEl = document.getElementById(`nada-edit-${id}`);

        if (viewEl && editEl) {
            editEl.classList.add('hidden');
            viewEl.classList.remove('hidden');
        }
    }

    async function saveEditNada(id) {
        clearNadaAlerts();
        const inputEl = document.getElementById(`nada-input-${id}`);
        const saveBtn = document.getElementById(`nada-save-btn-${id}`);
        const newName = inputEl.value.trim().toLowerCase();

        if (!newName) {
            showNadaError('Nama nada tidak boleh kosong.');
            return;
        }

        const oldName = document.getElementById(`nada-text-${id}`).textContent.trim().toLowerCase();
        if (newName === oldName) {
            cancelEditNada(id);
            return;
        }

        if (saveBtn) saveBtn.disabled = true;

        try {
            const response = await fetch(`/admin/nadas/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ nada: newName })
            });

            const result = await response.json();

            if (!response.ok) {
                const message = result.message || (result.errors && result.errors.nada ? result.errors.nada[0] : 'Gagal memperbarui nada.');
                showNadaError(message);
                if (saveBtn) saveBtn.disabled = false;
                return;
            }

            const updatedNada = result.data.nada;
            const nadaCapitalized = updatedNada.charAt(0).toUpperCase() + updatedNada.slice(1);

            // Update row text and onclick attribute
            document.getElementById(`nada-text-${id}`).textContent = nadaCapitalized;
            const viewEl = document.getElementById(`nada-view-${id}`);
            const editBtn = viewEl.querySelector('button[title="Edit Nada"]');
            if (editBtn) {
                editBtn.setAttribute('onclick', `startEditNada(${id}, '${updatedNada.replace(/'/g, "\\'")}')`);
            }
            const delBtn = viewEl.querySelector('button[title="Hapus Nada"]');
            if (delBtn) {
                delBtn.setAttribute('onclick', `confirmDeleteNada(${id}, '${updatedNada.replace(/'/g, "\\'")}')`);
            }

            // Update dropdowns in song modals
            ['create_songnada', 'edit_songnada'].forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    for (let opt of select.options) {
                        if (opt.value.toLowerCase() === oldName) {
                            opt.value = updatedNada;
                            opt.textContent = nadaCapitalized;
                        }
                    }
                }
            });

            cancelEditNada(id);
            showNadaSuccess(`Nada berhasil diubah menjadi "${nadaCapitalized}".`);
        } catch (err) {
            showNadaError('Terjadi kesalahan saat memperbarui nada.');
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    }

    async function confirmDeleteNada(id, name) {
        clearNadaAlerts();
        if (!confirm(`Apakah Anda yakin ingin menghapus nada "${name}"?`)) {
            return;
        }

        try {
            const response = await fetch(`/admin/nadas/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                const message = result.message || 'Gagal menghapus nada.';
                showNadaError(message);
                return;
            }

            // Remove row from list
            const row = document.getElementById(`nada-row-${id}`);
            if (row) row.remove();

            // Update dropdowns: remove option
            const lowerName = name.toLowerCase();
            ['create_songnada', 'edit_songnada'].forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    for (let i = select.options.length - 1; i >= 0; i--) {
                        if (select.options[i].value.toLowerCase() === lowerName) {
                            const wasSelected = select.options[i].selected;
                            select.remove(i);
                            if (wasSelected) {
                                select.value = '-';
                            }
                        }
                    }
                }
            });

            // If list empty, show empty state
            const listContainer = document.getElementById('nadaListContainer');
            if (listContainer && listContainer.querySelectorAll('li[id^="nada-row-"]').length === 0) {
                listContainer.innerHTML = '<li id="nadaEmptyState" class="text-center py-6 text-xs text-slate-400">Belum ada nada yang tersimpan.</li>';
            }

            updateNadaCountBadge();
            showNadaSuccess(`Nada "${name}" berhasil dihapus.`);
        } catch (err) {
            showNadaError('Terjadi kesalahan saat menghapus nada.');
        }
    }

    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.getElementById('create_songtitle').focus();
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function openEditModal(song) {
        const form = document.getElementById('editForm');
        form.action = `/admin/songs/${song.songid}`;
        
        document.getElementById('edit_songtitle').value = song.songtitle || '';
        document.getElementById('edit_songsinger').value = song.songsinger || '';
        document.getElementById('edit_songcategory').value = song.songcategory || '';
        document.getElementById('edit_songnada').value = song.songnada || '-';
        document.getElementById('edit_songduration').value = song.songduration || '';
        document.getElementById('edit_songurl').value = song.songurl || '';

        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('edit_songtitle').focus();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openDeleteModal(id, title) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/songs/${id}`;
        document.getElementById('deleteSongTitle').textContent = title;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Handle AJAX submission for Tambah Nada to prevent parent modals from resetting
    document.getElementById('createNadaForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearNadaAlerts();

        const submitBtn = document.getElementById('createNadaSubmitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Menyimpan...';

        const inputNada = document.getElementById('create_nada_name');
        const nadaValue = inputNada.value.trim().toLowerCase();

        try {
            const response = await fetch("{{ route('admin.nadas.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ nada: nadaValue })
            });

            const result = await response.json();

            if (!response.ok) {
                const message = result.message || (result.errors && result.errors.nada ? result.errors.nada[0] : 'Gagal menambahkan nada.');
                showNadaError(message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }

            const newNada = result.data.nada;
            const newId = result.data.id;
            const nadaCapitalized = newNada.charAt(0).toUpperCase() + newNada.slice(1);

            // Add row to nada list container
            const emptyState = document.getElementById('nadaEmptyState');
            if (emptyState) emptyState.remove();

            const listContainer = document.getElementById('nadaListContainer');
            if (listContainer && !document.getElementById(`nada-row-${newId}`)) {
                const newLi = document.createElement('li');
                newLi.id = `nada-row-${newId}`;
                newLi.className = 'pt-1 first:pt-0';
                newLi.innerHTML = `
                    <div id="nada-view-${newId}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-800/40 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="h-2 w-2 rounded-full bg-blue-500 shrink-0"></span>
                            <span id="nada-text-${newId}" class="text-sm font-medium text-white capitalize">${nadaCapitalized}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" 
                                    onclick="startEditNada(${newId}, '${newNada.replace(/'/g, "\\'")}')" 
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-800 transition-colors" 
                                    title="Edit Nada">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button type="button" 
                                    onclick="confirmDeleteNada(${newId}, '${newNada.replace(/'/g, "\\'")}')" 
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors" 
                                    title="Hapus Nada">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div id="nada-edit-${newId}" class="hidden flex items-center gap-2 p-1.5 bg-slate-900/90 rounded-lg border border-slate-700/60">
                        <input type="text" 
                               id="nada-input-${newId}" 
                               value="${newNada}" 
                               onkeydown="if(event.key === 'Enter'){ event.preventDefault(); saveEditNada(${newId}); } if(event.key === 'Escape'){ event.preventDefault(); cancelEditNada(${newId}); }"
                               class="flex-1 rounded-lg border border-slate-700 bg-slate-950 px-2.5 py-1.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500">
                        <button type="button" 
                                id="nada-save-btn-${newId}"
                                onclick="saveEditNada(${newId})" 
                                class="p-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white transition-colors" 
                                title="Simpan Perubahan">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </button>
                        <button type="button" 
                                onclick="cancelEditNada(${newId})" 
                                class="p-1.5 rounded-lg border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" 
                                title="Batal">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                `;
                listContainer.appendChild(newLi);
            }

            // Update dropdowns
            ['create_songnada', 'edit_songnada'].forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    let exists = false;
                    for (let i = 0; i < select.options.length; i++) {
                        if (select.options[i].value.toLowerCase() === newNada.toLowerCase()) {
                            exists = true;
                            break;
                        }
                    }
                    if (!exists) {
                        const opt = new Option(nadaCapitalized, newNada);
                        select.add(opt);
                    }
                }
            });

            // Auto-select in active modal
            const createSelect = document.getElementById('create_songnada');
            if (createSelect && !document.getElementById('createModal').classList.contains('hidden')) {
                createSelect.value = newNada;
            }

            const editSelect = document.getElementById('edit_songnada');
            if (editSelect && !document.getElementById('editModal').classList.contains('hidden')) {
                editSelect.value = newNada;
            }

            inputNada.value = '';
            updateNadaCountBadge();
            showNadaSuccess(`Nada "${nadaCapitalized}" berhasil ditambahkan.`);
        } catch (err) {
            showNadaError('Terjadi kesalahan saat memproses data.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const nadaModal = document.getElementById('createNadaModal');
            if (nadaModal && !nadaModal.classList.contains('hidden')) {
                closeCreateNadaModal();
                return;
            }
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });
</script>
@endsection
