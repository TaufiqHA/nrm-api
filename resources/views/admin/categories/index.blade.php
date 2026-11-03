@extends('layouts.admin')

@section('page-title', 'Kategori Lagu')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Kategori Lagu</h1>
            <p class="text-sm text-slate-300 mt-1">Kelola daftar kategori dan genre musik karaoke</p>
        </div>
        <div>
            <button type="button" 
                    onclick="openCreateModal()" 
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Kategori</span>
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
    <div class="flex items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="w-full max-w-xl">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama kategori lagu..." 
                       class="block w-full h-12 rounded-xl border border-slate-800 bg-slate-900 pl-12 pr-4 text-base text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>
        </form>

        @if(request('search'))
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-slate-400 hover:text-slate-200 underline">
                Reset Pencarian
            </a>
        @endif
    </div>

    <!-- Data Table Container -->
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/60 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-20 text-center">Urutan</th>
                        <th scope="col" class="px-6 py-4 w-20">ID</th>
                        <th scope="col" class="px-6 py-4 w-20 text-center">Ikon</th>
                        <th scope="col" class="px-6 py-4">Nama Kategori</th>
                        <th scope="col" class="px-6 py-4 text-center">Jumlah Lagu</th>
                        <th scope="col" class="px-6 py-4 text-center">Status</th>
                        <th scope="col" class="px-6 py-4">Dibuat Pada</th>
                        <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 text-center font-mono font-semibold text-slate-300">
                                {{ $category->sort_order }}
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-400">
                                #{{ $category->songcategoryid }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 bg-slate-950 text-slate-200">
                                    @if ($category->icon_url)
                                        <img src="{{ $category->icon_url }}" alt="Ikon {{ $category->songcategoryname }}" class="h-6 w-6 object-contain">
                                    @elseif ($category->icon)
                                        <span class="material-symbols-rounded text-xl">{{ $category->icon }}</span>
                                    @else
                                        <span class="material-symbols-rounded text-xl text-slate-500" title="Otomatis">auto_awesome</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-white">
                                {{ $category->songcategoryname }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-lg bg-slate-800 px-2.5 py-1 text-xs font-mono font-medium text-slate-300">
                                    {{ $category->songs_count }} lagu
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('admin.categories.update-status', $category) }}" class="inline-flex">
                                    @csrf
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                               name="is_active"
                                               value="1"
                                               onchange="this.form.submit()"
                                               @checked($category->is_active)
                                               class="peer sr-only">
                                        <span class="relative h-5 w-9 rounded-full bg-slate-700 transition-colors peer-checked:bg-blue-600 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-slate-900 after:absolute after:top-0.5 after:left-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-transform peer-checked:after:translate-x-4"></span>
                                        <span class="text-xs font-medium {{ $category->is_active ? 'text-emerald-400' : 'text-slate-500' }}">
                                            {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </label>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-slate-400 text-xs font-mono">
                                {{ $category->created_at ? $category->created_at->format('d M Y, H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Move Up -->
                                    <form method="POST" action="{{ route('admin.categories.move', $category) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit"
                                                class="p-2 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-800 transition-colors disabled:opacity-30 disabled:hover:text-slate-400 disabled:hover:bg-transparent disabled:cursor-not-allowed"
                                                title="Naikkan Urutan"
                                                @disabled($category->sort_order <= $minSortOrder)>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Move Down -->
                                    <form method="POST" action="{{ route('admin.categories.move', $category) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit"
                                                class="p-2 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-800 transition-colors disabled:opacity-30 disabled:hover:text-slate-400 disabled:hover:bg-transparent disabled:cursor-not-allowed"
                                                title="Turunkan Urutan"
                                                @disabled($category->sort_order >= $maxSortOrder)>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Edit Button -->
                                    <button type="button" 
                                            onclick="openEditModal({{ $category->songcategoryid }}, @js($category->songcategoryname), @js($category->icon), @js($category->icon_url))"
                                            class="p-2 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-800 transition-colors"
                                            title="Edit Kategori">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            onclick="openDeleteModal({{ $category->songcategoryid }}, '{{ addslashes($category->songcategoryname) }}', {{ $category->songs_count }})"
                                            class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors"
                                            title="Hapus Kategori">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="h-8 w-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                    </svg>
                                    <p class="text-sm font-medium">Belum ada kategori yang ditemukan</p>
                                    @if(request('search'))
                                        <p class="text-xs text-slate-500">Tidak ada kategori yang cocok dengan kata kunci "{{ request('search') }}"</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="border-t border-slate-800 p-4">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Create Category Modal -->
<div id="createModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-sm sm:max-w-md max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-semibold text-white">Tambah Kategori Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="create_category_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Nama Kategori
                </label>
                <input type="text" 
                       name="songcategoryname" 
                       id="create_category_name" 
                       required 
                       placeholder="Contoh: Pop, Rock, Dangdut"
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            @include('admin.categories._icon_picker', ['prefix' => 'create'])

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        onclick="closeCreateModal()" 
                        class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-sm sm:max-w-md max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-semibold text-white">Edit Kategori</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_category_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Nama Kategori
                </label>
                <input type="text" 
                       name="songcategoryname" 
                       id="edit_category_name" 
                       required 
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            @include('admin.categories._icon_picker', ['prefix' => 'edit'])

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        onclick="closeEditModal()" 
                        class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors">
                    Perbarui Kategori
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
                <h3 class="text-base font-semibold text-white">Hapus Kategori</h3>
                <p class="text-xs text-slate-400">Tindakan ini tidak dapat dibatalkan</p>
            </div>
        </div>

        <p class="text-sm text-slate-300">
            Apakah Anda yakin ingin menghapus kategori <strong id="deleteCategoryName" class="text-white"></strong>?
        </p>

        <div id="deleteWarning" class="hidden rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-xs text-amber-300">
            Peringatan: Kategori ini memiliki lagu yang terhubung. Pastikan lagu dipindahkan terlebih dahulu.
        </div>

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
                Hapus
            </button>
        </form>
    </div>
</div>

<script>
    const ICON_SOURCE_ACTIVE = ['border-blue-500', 'bg-blue-600/20', 'text-blue-300'];
    const ICON_SOURCE_IDLE = ['border-slate-800', 'bg-slate-950', 'text-slate-400'];
    const ICON_KEY_ACTIVE = ['border-blue-500', 'bg-blue-600/20', 'text-blue-300'];
    const ICON_KEY_IDLE = ['border-transparent', 'text-slate-300'];

    function getIconPicker(prefix) {
        return document.querySelector(`[data-icon-picker][data-prefix="${prefix}"]`);
    }

    function toggleClasses(element, classes, enabled) {
        classes.forEach((name) => element.classList.toggle(name, enabled));
    }

    function setIconSource(prefix, source) {
        const root = getIconPicker(prefix);
        if (!root) return;

        const input = root.querySelector(`#${prefix}_icon_source`);
        if (input) input.value = source;

        root.querySelectorAll('[data-icon-source]').forEach((button) => {
            const active = button.dataset.iconSource === source;
            toggleClasses(button, ICON_SOURCE_ACTIVE, active);
            toggleClasses(button, ICON_SOURCE_IDLE, !active);
        });

        root.querySelectorAll('[data-icon-section]').forEach((section) => {
            section.classList.toggle('hidden', section.dataset.iconSection !== source);
        });
    }

    function setIconKey(prefix, key) {
        const root = getIconPicker(prefix);
        if (!root) return;

        const input = root.querySelector(`#${prefix}_icon`);
        if (input) input.value = key || '';

        let labelText = '';
        root.querySelectorAll('[data-icon-key]').forEach((button) => {
            const active = button.dataset.iconKey === key;
            toggleClasses(button, ICON_KEY_ACTIVE, active);
            toggleClasses(button, ICON_KEY_IDLE, !active);
            if (active) labelText = button.getAttribute('title') || key;
        });

        const label = root.querySelector('[data-icon-key-label]');
        if (label) label.textContent = labelText;
    }

    function previewIconFile(prefix) {
        const root = getIconPicker(prefix);
        if (!root) return;

        const input = root.querySelector(`#${prefix}_icon_file`);
        const preview = root.querySelector('[data-icon-preview]');
        if (!preview) return;

        const image = preview.querySelector('img');
        if (input && input.files && input.files[0]) {
            image.src = URL.createObjectURL(input.files[0]);
            preview.classList.remove('hidden');
            preview.classList.add('flex');
        } else {
            image.removeAttribute('src');
            preview.classList.add('hidden');
            preview.classList.remove('flex');
        }
    }

    function resetIconPicker(prefix, source, iconKey, imageUrl) {
        setIconSource(prefix, source || 'auto');
        setIconKey(prefix, iconKey || '');

        const root = getIconPicker(prefix);
        if (!root) return;

        const file = root.querySelector(`#${prefix}_icon_file`);
        if (file) file.value = '';

        const preview = root.querySelector('[data-icon-preview]');
        if (preview) {
            const image = preview.querySelector('img');
            if (imageUrl) {
                image.src = imageUrl;
                preview.classList.remove('hidden');
                preview.classList.add('flex');
            } else {
                image.removeAttribute('src');
                preview.classList.add('hidden');
                preview.classList.remove('flex');
            }
        }

        const removeWrap = root.querySelector('[data-icon-remove]');
        if (removeWrap) {
            const checkbox = removeWrap.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
            removeWrap.classList.toggle('hidden', !imageUrl);
            removeWrap.classList.toggle('flex', !!imageUrl);
        }
    }

    function openCreateModal() {
        resetIconPicker('create', 'auto', '', null);
        document.getElementById('createModal').classList.remove('hidden');
        document.getElementById('create_category_name').focus();
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function openEditModal(id, name, icon, iconUrl) {
        const form = document.getElementById('editForm');
        form.action = `/admin/categories/${id}`;
        const input = document.getElementById('edit_category_name');
        input.value = name;

        const source = iconUrl ? 'image' : (icon ? 'builtin' : 'auto');
        resetIconPicker('edit', source, icon, iconUrl);

        document.getElementById('editModal').classList.remove('hidden');
        input.focus();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openDeleteModal(id, name, songCount) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/categories/${id}`;
        document.getElementById('deleteCategoryName').textContent = name;
        
        const warning = document.getElementById('deleteWarning');
        if (songCount > 0) {
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
        }

        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Close modals on Escape key
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });
</script>
@endsection
