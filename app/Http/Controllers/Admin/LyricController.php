<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LyricController extends Controller
{
    /**
     * Display the lyric management list.
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

        if ($request->filled('status')) {
            if ($request->input('status') === 'filled') {
                $query->whereNotNull('songlyric')->where('songlyric', '!=', '');
            } elseif ($request->input('status') === 'empty') {
                $query->where(function ($q) {
                    $q->whereNull('songlyric')->orWhere('songlyric', '');
                });
            }
        }

        $songs = $query
            ->orderByRaw('CAST(songsinger AS UNSIGNED) ASC')
            ->orderBy('songtitle')
            ->paginate(10)
            ->withQueryString();
        $categories = Category::orderBy('sort_order')->orderBy('songcategoryname')->get();

        return view('admin.lyrics.index', compact('songs', 'categories'));
    }

    /**
     * Update the lyric of the specified song.
     */
    public function update(Request $request, Song $song): RedirectResponse
    {
        $validated = $request->validate([
            'songlyric' => ['nullable', 'string'],
        ], [
            'songlyric.string' => 'Lirik harus berupa teks.',
        ]);

        $lyric = trim((string) ($validated['songlyric'] ?? ''));

        $song->update([
            'songlyric' => $lyric === '' ? null : $lyric,
        ]);

        return redirect()
            ->route('admin.lyrics.index', $request->only(['search', 'category', 'status', 'page']))
            ->with('success', "Lirik \"{$song->songtitle}\" berhasil diperbarui.");
    }
}
