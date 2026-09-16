<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SongController extends Controller
{
    /**
     * Display a listing of songs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Song::query()->with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('songtitle', 'like', '%'.$search.'%')
                    ->orWhere('songsinger', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('songcategory')) {
            $query->where('songcategory', $request->input('songcategory'));
        } elseif ($request->filled('category_id')) {
            $query->where('songcategory', $request->input('category_id'));
        }

        $query->orderBy('songtitle');

        if ($request->has('page')) {
            $paginated = $query->paginate($request->integer('per_page', 15));

            return response()->json($paginated);
        }

        $songs = $query->get();

        return response()->json([
            'data' => $songs,
        ]);
    }

    /**
     * Store a newly created song in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'songtitle' => ['required', 'string', 'max:255'],
            'songsinger' => ['required', 'string', 'max:255'],
            'songurl' => ['required', 'string'],
            'songcategory' => ['required', 'integer', 'exists:categories,songcategoryid'],
            'songnada' => ['nullable', 'string', 'max:10'],
            'songduration' => ['nullable', 'string', 'max:5'],
        ]);

        $song = Song::create($validated);

        return response()->json([
            'message' => 'Song created successfully',
            'data' => $song->load('category'),
        ], 201);
    }

    /**
     * Display the specified song.
     */
    public function show(Song $song): JsonResponse
    {
        return response()->json([
            'data' => $song->load('category'),
        ]);
    }

    /**
     * Update the specified song in storage.
     */
    public function update(Request $request, Song $song): JsonResponse
    {
        $validated = $request->validate([
            'songtitle' => ['sometimes', 'required', 'string', 'max:255'],
            'songsinger' => ['sometimes', 'required', 'string', 'max:255'],
            'songurl' => ['sometimes', 'required', 'string'],
            'songcategory' => ['sometimes', 'required', 'integer', 'exists:categories,songcategoryid'],
            'songnada' => ['nullable', 'string', 'max:10'],
            'songduration' => ['nullable', 'string', 'max:5'],
        ]);

        $song->update($validated);

        return response()->json([
            'message' => 'Song updated successfully',
            'data' => $song->fresh()->load('category'),
        ]);
    }

    /**
     * Remove the specified song from storage.
     */
    public function destroy(Song $song): JsonResponse
    {
        $song->delete();

        return response()->json([
            'message' => 'Song deleted successfully',
        ]);
    }
}
