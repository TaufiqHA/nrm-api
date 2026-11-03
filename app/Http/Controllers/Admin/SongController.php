<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadSongMedia;
use App\Models\Category;
use App\Models\Nada;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SongController extends Controller
{
    /**
     * Display a listing of songs.
     */
    public function index(Request $request): View
    {
        $query = Song::with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('songtitle', 'like', "%{$search}%")
                    ->orWhere('songsinger', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('songcategory', $request->input('category'));
        }

        $songs = $query
            ->orderByRaw('CAST(songsinger AS UNSIGNED) ASC')
            ->orderBy('songtitle')
            ->paginate(10)
            ->withQueryString();
        $categories = Category::orderBy('sort_order')->orderBy('songcategoryname')->get();
        $nadas = Nada::orderBy('nada')->get();

        return view('admin.songs.index', compact('songs', 'categories', 'nadas'));
    }

    /**
     * Store a newly created song in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $allowedNadas = array_unique(array_merge(['pria', 'wanita', '-'], Nada::pluck('nada')->toArray()));

        $validated = $request->validate([
            'songtitle' => ['required', 'string', 'max:255'],
            'songsinger' => ['required', 'string', 'max:255'],
            'songcategory' => ['required', 'integer', 'exists:categories,songcategoryid'],
            'songnada' => ['nullable', 'string', Rule::in($allowedNadas)],
            'songduration' => ['nullable', 'string', 'max:5'],
            'songurl' => ['required', 'string'],
        ], [
            'songtitle.required' => 'Judul lagu wajib diisi.',
            'songsinger.required' => 'Pencipta wajib diisi.',
            'songcategory.required' => 'Kategori lagu wajib dipilih.',
            'songcategory.exists' => 'Kategori yang dipilih tidak valid.',
            'songurl.required' => 'URL lagu wajib diisi.',
            'songnada.in' => 'Pilihan nada tidak valid.',
        ]);

        $validated['songyoutubeurl'] = Song::isYouTubeLink($validated['songurl'])
            ? $validated['songurl']
            : null;

        $song = Song::create($validated);

        $this->queueMediaDownload($song);

        return redirect()->route('admin.songs.index')->with('success', 'Lagu berhasil ditambahkan ke katalog.');
    }

    /**
     * Update the specified song in storage.
     */
    public function update(Request $request, Song $song): RedirectResponse
    {
        $allowedNadas = array_unique(array_merge(['pria', 'wanita', '-'], Nada::pluck('nada')->toArray()));

        $validated = $request->validate([
            'songtitle' => ['required', 'string', 'max:255'],
            'songsinger' => ['required', 'string', 'max:255'],
            'songcategory' => ['required', 'integer', 'exists:categories,songcategoryid'],
            'songnada' => ['nullable', 'string', Rule::in($allowedNadas)],
            'songduration' => ['nullable', 'string', 'max:5'],
            'songurl' => ['required', 'string'],
        ], [
            'songtitle.required' => 'Judul lagu wajib diisi.',
            'songsinger.required' => 'Pencipta wajib diisi.',
            'songcategory.required' => 'Kategori lagu wajib dipilih.',
            'songcategory.exists' => 'Kategori yang dipilih tidak valid.',
            'songurl.required' => 'URL lagu wajib diisi.',
            'songnada.in' => 'Pilihan nada tidak valid.',
        ]);

        $submittedUrl = $validated['songurl'];
        $isYoutube = Song::isYouTubeLink($submittedUrl);
        $previousYoutubeUrl = $song->songyoutubeurl;

        if ($isYoutube) {
            $validated['songyoutubeurl'] = $submittedUrl;

            if ($previousYoutubeUrl === $submittedUrl) {
                // Link YouTube tidak berubah: pertahankan URL file lokal di songurl.
                unset($validated['songurl']);
            }
        } else {
            $validated['songyoutubeurl'] = null;
        }

        $song->update($validated);

        if ($isYoutube && $previousYoutubeUrl !== $submittedUrl) {
            $this->queueMediaDownload($song, force: true);
        }

        return redirect()->route('admin.songs.index')->with('success', 'Data lagu berhasil diperbarui.');
    }

    /**
     * Queue downloads for every song that still needs local media.
     */
    public function downloadAll(): RedirectResponse
    {
        $songs = Song::query()
            ->whereNotNull('songyoutubeurl')
            ->where(function ($query) {
                $query->whereNull('download_status')
                    ->orWhere('download_status', 'failed');
            })
            ->get();

        if ($songs->isEmpty()) {
            return redirect()->route('admin.songs.index')
                ->with('success', 'Tidak ada lagu yang perlu diunduh.');
        }

        $songs->each(fn (Song $song) => $this->queueMediaDownload($song, force: true));

        return redirect()->route('admin.songs.index')
            ->with('success', "{$songs->count()} lagu berhasil dimasukkan ke antrean unduhan.");
    }

    /**
     * Reset download state and dispatch the media download job.
     */
    protected function queueMediaDownload(Song $song, bool $force = false): void
    {
        $sourceUrl = $song->songyoutubeurl;

        if (! Song::isYouTubeLink($sourceUrl)) {
            return;
        }

        if (! $force && $song->isDownloadInProgress()) {
            return;
        }

        $song->forceFill([
            'download_status' => 'pending',
            'download_progress' => 0,
            'download_error' => null,
        ])->save();

        DownloadSongMedia::dispatch($song->songid, $sourceUrl);
    }

    /**
     * Remove the specified song from storage.
     */
    public function destroy(Song $song): RedirectResponse
    {
        $song->delete();

        return redirect()->route('admin.songs.index')->with('success', 'Lagu berhasil dihapus dari katalog.');
    }
}
