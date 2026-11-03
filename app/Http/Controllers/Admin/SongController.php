<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadSongMedia;
use App\Models\Category;
use App\Models\Nada;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
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

        $song->update($validated);

        if ($song->wasChanged('songurl')) {
            $this->queueMediaDownload($song, force: true);
        }

        return redirect()->route('admin.songs.index')->with('success', 'Data lagu berhasil diperbarui.');
    }

    /**
     * Queue a fresh download for the given song.
     */
    public function download(Song $song): RedirectResponse
    {
        if (! $song->isYouTubeUrl()) {
            return redirect()->route('admin.songs.index')
                ->with('error', 'URL lagu bukan link YouTube, unduhan tidak dapat dijalankan.');
        }

        $this->queueMediaDownload($song, force: true);

        return redirect()->route('admin.songs.index')
            ->with('success', "Unduhan untuk \"{$song->songtitle}\" berhasil dimasukkan ke antrean.");
    }

    /**
     * Return download progress for the requested songs.
     */
    public function downloadStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer'],
        ]);

        $query = Song::query()->select([
            'songid',
            'songtitle',
            'songurl',
            'songvideourl',
            'download_status',
            'download_progress',
            'download_error',
            'downloaded_at',
        ]);

        if (! empty($validated['ids'])) {
            $query->whereIn('songid', $validated['ids']);
        } else {
            $query->whereNotNull('download_status');
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    /**
     * Reset download state and dispatch the media download job.
     */
    protected function queueMediaDownload(Song $song, bool $force = false): void
    {
        if (! $song->isYouTubeUrl()) {
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

        DownloadSongMedia::dispatch($song->songid, $song->songurl);
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
