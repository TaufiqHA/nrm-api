<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $songs = $query->orderBy('songtitle')->paginate(10)->withQueryString();
        $categories = Category::orderBy('songcategoryname')->get();
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

        Song::create($validated);

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

        return redirect()->route('admin.songs.index')->with('success', 'Data lagu berhasil diperbarui.');
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
