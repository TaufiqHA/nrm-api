@extends('layouts.admin')

@section('page-title', 'Musik')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Musik</h1>
            <p class="text-sm text-slate-300 mt-1">Kelola file MP3 yang diputar di menu Musik aplikasi</p>
        </div>
        <div>
            <button type="button"
                    onclick="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Musik</span>
            </button>
        </div>
    </div>

    @if (! $driveConfigured)
        <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm font-medium text-amber-300 flex items-start gap-2.5">
            <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>Kredensial Google Drive belum lengkap. Isi <code class="font-mono">GOOGLE_DRIVE_CLIENT_ID</code>, <code class="font-mono">GOOGLE_DRIVE_CLIENT_SECRET</code>, <code class="font-mono">GOOGLE_DRIVE_REFRESH_TOKEN</code>, dan <code class="font-mono">GOOGLE_DRIVE_FOLDER_ID</code> di file <code class="font-mono">.env</code> sebelum mengunggah musik.</span>
        </div>
    @endif

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

    <!-- Status unggahan latar belakang -->
    @if ($music->contains(fn ($item) => in_array($item->upload_status ?? 'ready', ['pending', 'processing'], true)))
        <div class="rounded-xl border border-blue-500/20 bg-blue-500/10 p-4 text-sm font-medium text-blue-300 flex items-center gap-2.5">
            <svg class="h-5 w-5 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span>Ada file musik yang sedang diunggah ke Google Drive. Halaman ini akan diperbarui otomatis setelah selesai. Jika status tidak berubah, pastikan queue worker sedang berjalan.</span>
        </div>
    @endif

    <!-- Search Bar -->
    <div class="flex items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.music.index') }}" class="w-full max-w-xl">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari judul atau artis..."
                       class="block w-full h-12 rounded-xl border border-slate-800 bg-slate-900 pl-12 pr-4 text-base text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>
        </form>

        @if(request('search'))
            <a href="{{ route('admin.music.index') }}" class="text-sm text-slate-400 hover:text-slate-200 underline">
                Reset Pencarian
            </a>
        @endif
    </div>

    <!-- Data Table -->
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/60 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-20">ID</th>
                        <th scope="col" class="px-6 py-4">Judul</th>
                        <th scope="col" class="px-6 py-4 text-center">File</th>
                        <th scope="col" class="px-6 py-4 text-center">Durasi</th>
                        <th scope="col" class="px-6 py-4 text-center">Status</th>
                        <th scope="col" class="px-6 py-4">Diunggah</th>
                        <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($music as $item)
                        @php
                            $seconds = (int) $item->musicduration;
                            $durationLabel = $seconds > 0
                                ? ($seconds >= 3600 ? gmdate('H:i:s', $seconds) : gmdate('i:s', $seconds))
                                : '—';
                            $uploadStatus = $item->upload_status ?? 'ready';
                            $isUploading = in_array($uploadStatus, ['pending', 'processing'], true);
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-mono text-slate-400">#{{ $item->musicid }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-white">{{ $item->musictitle }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5 font-mono">{{ $item->musicfilename }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($uploadStatus === 'ready')
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        Siap
                                    </span>
                                @elseif ($isUploading)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-300">
                                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Mengunggah
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-rose-500/10 px-2.5 py-1 text-xs font-medium text-rose-400" title="{{ $item->upload_error }}">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        Gagal
                                    </span>
                                    <p class="mx-auto mt-1 max-w-[160px] truncate text-[10px] text-rose-400" title="{{ $item->upload_error }}">
                                        {{ $item->upload_error }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-slate-300">{{ $durationLabel }}</td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('admin.music.update-status', $item) }}" class="inline-flex">
                                    @csrf
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                               name="is_active"
                                               value="1"
                                               {{ $item->is_active ? 'checked' : '' }}
                                               onchange="this.form.submit()"
                                               class="h-4 w-4 rounded border-slate-600 bg-slate-950 text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs font-medium {{ $item->is_active ? 'text-emerald-400' : 'text-slate-500' }}">
                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </label>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                {{ $item->created_at?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            onclick="openEditModal({{ $item->musicid }}, @js($item->musictitle), {{ $item->is_active ? 'true' : 'false' }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:border-blue-500 hover:text-blue-400 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                        Edit
                                    </button>
                                    <button type="button"
                                            onclick="openDeleteModal({{ $item->musicid }}, @js($item->musictitle))"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-500/30 px-2.5 py-1.5 text-xs font-medium text-rose-400 hover:bg-rose-500/10 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-10 w-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9a3 3 0 1 1-3 3V6.75A2.25 2.25 0 0 1 8.25 4.5h6A2.25 2.25 0 0 1 16.5 6.75v8.25a3 3 0 1 1-3-3V9m-4.5 3H9" />
                                    </svg>
                                    <span>Belum ada musik. Unggah file MP3 pertama Anda.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($music->hasPages())
        <div class="pt-2">
            {{ $music->links() }}
        </div>
    @endif
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" onclick="closeCreateModal()"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 px-6 py-4">
                <h2 class="text-lg font-bold text-white">Tambah Musik</h2>
                <button type="button" onclick="closeCreateModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-800 hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.music.store') }}" enctype="multipart/form-data" class="space-y-4 px-6 py-5">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Judul Musik <span class="text-rose-400">*</span></label>
                    <input type="text" name="musictitle" value="{{ old('musictitle') }}" required maxlength="255"
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">File MP3 <span class="text-rose-400">*</span></label>
                    <input type="file" name="file" accept="audio/mpeg,.mp3" required
                           class="block w-full text-xs text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-xs file:font-medium file:text-white hover:file:bg-blue-500">
                    <p class="mt-1 text-[11px] text-slate-500">Maksimal 50 MB. File akan diunggah ke Google Drive. Batas server: upload_max_filesize {{ ini_get('upload_max_filesize') }}, post_max_size {{ ini_get('post_max_size') }}.</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-600 bg-slate-950 text-blue-600 focus:ring-blue-500">
                    Tampilkan di aplikasi
                </label>
                <div class="flex items-center justify-end gap-3 border-t border-slate-800 pt-4">
                    <button type="button" onclick="closeCreateModal()" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800">Batal</button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500">Unggah Musik</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" onclick="closeEditModal()"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 px-6 py-4">
                <h2 class="text-lg font-bold text-white">Edit Musik</h2>
                <button type="button" onclick="closeEditModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-800 hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4 px-6 py-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Judul Musik <span class="text-rose-400">*</span></label>
                    <input type="text" name="musictitle" id="edit_musictitle" required maxlength="255"
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Ganti File MP3</label>
                    <input type="file" name="file" accept="audio/mpeg,.mp3"
                           class="block w-full text-xs text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-xs file:font-medium file:text-slate-200 hover:file:bg-slate-700">
                    <p class="mt-1 text-[11px] text-slate-500">Biarkan kosong bila tidak ingin mengganti file. Batas server: upload_max_filesize {{ ini_get('upload_max_filesize') }}, post_max_size {{ ini_get('post_max_size') }}.</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="h-4 w-4 rounded border-slate-600 bg-slate-950 text-blue-600 focus:ring-blue-500">
                    Tampilkan di aplikasi
                </label>
                <div class="flex items-center justify-end gap-3 border-t border-slate-800 pt-4">
                    <button type="button" onclick="closeEditModal()" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800">Batal</button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" onclick="closeDeleteModal()"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl">
            <div class="px-6 py-5">
                <h2 class="text-lg font-bold text-white">Hapus Musik</h2>
                <p class="mt-2 text-sm text-slate-300">
                    Yakin ingin menghapus <strong id="deleteMusicTitle" class="text-white"></strong>?
                    File MP3 akan dihapus dari Google Drive dan tidak dapat dikembalikan.
                </p>
            </div>
            <form id="deleteForm" method="POST" class="flex items-center justify-end gap-3 border-t border-slate-800 px-6 py-4">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800">Batal</button>
                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-rose-500">Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.querySelector('#createModal input[name="musictitle"]').focus();
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function openEditModal(id, title, isActive) {
        const form = document.getElementById('editForm');
        form.action = `/admin/music/${id}`;
        document.getElementById('edit_musictitle').value = title;
        document.getElementById('edit_is_active').checked = isActive;

        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('edit_musictitle').focus();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openDeleteModal(id, title) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/music/${id}`;
        document.getElementById('deleteMusicTitle').textContent = title;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });

    @if ($music->contains(fn ($item) => in_array($item->upload_status ?? 'ready', ['pending', 'processing'], true)))
        setTimeout(function () {
            const editing = !document.getElementById('editModal').classList.contains('hidden');
            const creating = !document.getElementById('createModal').classList.contains('hidden');
            const deleting = !document.getElementById('deleteModal').classList.contains('hidden');

            if (!document.hidden && !editing && !creating && !deleting) {
                window.location.reload();
            }
        }, 6000);
    @endif
</script>
@endsection
