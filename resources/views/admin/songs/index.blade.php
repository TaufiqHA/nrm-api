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

<!-- Modalbox Tambah Nada -->
<div id="createNadaModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden" onclick="closeCreateNadaModal()">
    <div class="relative w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl space-y-5" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-lg font-bold text-white tracking-tight">Tambah Nada Baru</h3>
                <p class="text-xs text-slate-400 mt-0.5">Tambahkan pilihan nada untuk katalog lagu</p>
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

        <form method="POST" action="{{ route('admin.nadas.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="create_nada_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Nama Nada <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       name="nada" 
                       id="create_nada_name" 
                       required 
                       placeholder="Contoh: pria, wanita, duet, anak"
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        onclick="closeCreateNadaModal()" 
                        class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                    Simpan Nada
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateNadaModal() {
        document.getElementById('createNadaModal').classList.remove('hidden');
        document.getElementById('create_nada_name').focus();
    }

    function closeCreateNadaModal() {
        document.getElementById('createNadaModal').classList.add('hidden');
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

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateNadaModal();
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });
</script>
@endsection
