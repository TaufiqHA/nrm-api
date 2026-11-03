@extends('layouts.admin')

@section('page-title', 'Lirik Lagu')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Lirik Lagu</h1>
            <p class="text-sm text-slate-300 mt-1">Kelola teks lirik untuk mode Teks Only di aplikasi</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-sm text-slate-300">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            <span>{{ $songs->total() }} lagu</span>
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

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm font-medium text-rose-400 space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('admin.lyrics.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full max-w-3xl">
            <div class="relative w-full sm:flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari judul lagu atau nomor lagu..."
                       class="block w-full h-12 rounded-xl border border-slate-800 bg-slate-900 pl-12 pr-4 text-base text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

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

            <div class="w-full sm:w-40 shrink-0">
                <select name="status"
                        onchange="this.form.submit()"
                        class="block w-full h-12 rounded-xl border border-slate-800 bg-slate-900 px-3 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    <option value="">Semua Status</option>
                    <option value="filled" {{ request('status') === 'filled' ? 'selected' : '' }}>Sudah ada lirik</option>
                    <option value="empty" {{ request('status') === 'empty' ? 'selected' : '' }}>Belum ada lirik</option>
                </select>
            </div>

            <button type="submit" class="hidden">Cari</button>
        </form>

        @if(request('search') || request('category') || request('status'))
            <a href="{{ route('admin.lyrics.index') }}" class="text-sm text-slate-400 hover:text-slate-200 underline">
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
                        <th scope="col" class="px-6 py-4">Judul Lagu &amp; Nomor Lagu</th>
                        <th scope="col" class="px-6 py-4">Kategori</th>
                        <th scope="col" class="px-6 py-4 text-center">Status Lirik</th>
                        <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($songs as $song)
                        @php($hasLyric = filled($song->songlyric))
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
                            <td class="px-6 py-4 text-center">
                                @if ($hasLyric)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400 border border-emerald-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Ada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-400 border border-slate-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                        Belum ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button"
                                        onclick="openLyricModal({{ $song->songid }})"
                                        class="inline-flex items-center gap-2 rounded-lg {{ $hasLyric ? 'bg-slate-800 text-slate-200 hover:bg-slate-700' : 'bg-blue-600 text-white hover:bg-blue-500' }} px-3 py-2 text-xs font-medium transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <span>{{ $hasLyric ? 'Edit Lirik' : 'Isi Lirik' }}</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="h-8 w-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <p class="text-sm font-medium">Belum ada lagu dalam katalog</p>
                                    <p class="text-xs text-slate-500">Tambahkan lagu terlebih dahulu melalui menu Lagu</p>
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

<!-- Edit Lyric Modal -->
<div id="lyricModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-2xl rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-start justify-between pb-3 border-b border-slate-800 gap-4">
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-white truncate" id="lyricModalTitle">Edit Lirik</h3>
                <p class="text-xs text-slate-400 mt-1" id="lyricModalSubtitle"></p>
            </div>
            <button type="button" onclick="closeLyricModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 shrink-0">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="lyricForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <div class="flex items-center justify-between gap-3 mb-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Daftar Ayat
                    </label>
                    <button type="button"
                            onclick="addVerseField()"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900 px-3 py-2 text-xs font-medium text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Tambah Ayat</span>
                    </button>
                </div>

                <div id="verseFields" class="space-y-3 max-h-[50vh] overflow-y-auto pr-1"></div>

                <input type="hidden" name="songlyric" id="songlyric">
                <p class="mt-2 text-xs text-slate-500">
                    Setiap kotak mewakili satu ayat. Ayat akan ditampilkan satu per satu di aplikasi dan diberi judul otomatis
                    <span class="font-semibold text-slate-300">Ayat 1</span>,
                    <span class="font-semibold text-slate-300">Ayat 2</span>, dst.
                </p>
            </div>

            <div class="flex items-center justify-between gap-3 pt-2">
                <span class="text-xs text-slate-500">Kosongkan semua ayat untuk menghapus lirik lagu ini.</span>
                <div class="flex items-center gap-3">
                    <button type="button"
                            onclick="closeLyricModal()"
                            class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors">
                        Simpan Lirik
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const lyricSongs = @json($songs->items());
    const headingPattern = /^(?:(?:ayat|bait)\s*\d+|reff|refrein|chorus|koor)$/i;

    function parseVerses(raw) {
        if (!raw) return [];

        return raw
            .split(/\n\s*\n/)
            .map((block) => block.trim())
            .filter((block) => block.length > 0)
            .map((block) => {
                const lines = block.split('\n');
                const firstLine = lines[0].trim();
                if (headingPattern.test(firstLine)) {
                    return lines.slice(1).join('\n').trim();
                }
                return block;
            })
            .filter((block) => block.length > 0);
    }

    function createVerseField(value = '') {
        const container = document.getElementById('verseFields');
        const field = document.createElement('div');
        field.className = 'verse-field rounded-xl border border-slate-800 bg-slate-950/60 p-3 space-y-2';
        field.innerHTML = `
            <div class="flex items-center justify-between gap-3">
                <span class="verse-label text-xs font-semibold uppercase tracking-wider text-slate-300"></span>
                <button type="button"
                        onclick="removeVerseField(this)"
                        class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-colors"
                        title="Hapus ayat ini">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Hapus</span>
                </button>
            </div>
            <textarea rows="4"
                      class="verse-input block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-3 text-sm leading-6 text-white placeholder-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors font-mono"
                      placeholder="Tulis lirik ayat ini..."></textarea>
        `;

        container.appendChild(field);
        field.querySelector('.verse-input').value = value;
        renumberVerses();

        return field;
    }

    function renumberVerses() {
        const fields = document.querySelectorAll('#verseFields .verse-field');
        fields.forEach((field, index) => {
            field.querySelector('.verse-label').textContent = 'Ayat ' + (index + 1);
        });
    }

    function addVerseField() {
        const field = createVerseField('');
        field.querySelector('.verse-input').focus();
        field.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function removeVerseField(button) {
        const field = button.closest('.verse-field');
        if (!field) return;

        field.remove();

        if (document.querySelectorAll('#verseFields .verse-field').length === 0) {
            createVerseField('');
        } else {
            renumberVerses();
        }
    }

    function openLyricModal(songId) {
        const song = lyricSongs.find((item) => Number(item.songid) === Number(songId));
        if (!song) return;

        const modal = document.getElementById('lyricModal');
        const form = document.getElementById('lyricForm');
        const title = document.getElementById('lyricModalTitle');
        const subtitle = document.getElementById('lyricModalSubtitle');
        const container = document.getElementById('verseFields');

        form.action = '{{ url('/admin/lyrics') }}/' + song.songid;
        title.textContent = 'Lirik: ' + song.songtitle;
        subtitle.textContent = 'Nomor lagu ' + song.songsinger;

        container.innerHTML = '';
        const verses = parseVerses(song.songlyric || '');
        (verses.length > 0 ? verses : ['']).forEach((verse) => createVerseField(verse));

        modal.classList.remove('hidden');
        const firstInput = container.querySelector('.verse-input');
        if (firstInput) firstInput.focus();
    }

    function closeLyricModal() {
        document.getElementById('lyricModal').classList.add('hidden');
    }

    document.getElementById('lyricForm').addEventListener('submit', function () {
        const values = Array.from(document.querySelectorAll('#verseFields .verse-input'))
            .map((input) => input.value.trim())
            .filter((value) => value.length > 0);

        document.getElementById('songlyric').value = values.join('\n\n');
    });

    document.getElementById('lyricModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeLyricModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeLyricModal();
        }
    });
</script>
@endsection
