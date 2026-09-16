<?php

namespace App\Http\Controllers;

use App\Models\Nada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NadaController extends Controller
{
    /**
     * Display a listing of nadas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Nada::query();

        if ($request->filled('search')) {
            $query->where('nada', 'like', '%'.$request->input('search').'%');
        }

        $nadas = $query->orderBy('nada')->get();

        return response()->json([
            'data' => $nadas,
        ]);
    }

    /**
     * Store a newly created nada in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nada' => ['required', 'string', 'max:50', 'unique:nadas,nada'],
        ]);

        $nada = Nada::create($validated);

        return response()->json([
            'message' => 'Nada created successfully',
            'data' => $nada,
        ], 201);
    }

    /**
     * Display the specified nada.
     */
    public function show(Nada $nada): JsonResponse
    {
        return response()->json([
            'data' => $nada,
        ]);
    }

    /**
     * Update the specified nada in storage.
     */
    public function update(Request $request, Nada $nada): JsonResponse
    {
        $validated = $request->validate([
            'nada' => [
                'required',
                'string',
                'max:50',
                Rule::unique('nadas', 'nada')->ignore($nada->id),
            ],
        ]);

        $nada->update($validated);

        return response()->json([
            'message' => 'Nada updated successfully',
            'data' => $nada->fresh(),
        ]);
    }

    /**
     * Remove the specified nada from storage.
     */
    public function destroy(Nada $nada): JsonResponse
    {
        if ($nada->songs()->exists()) {
            return response()->json([
                'message' => 'Nada cannot be deleted because it is still used by one or more songs.',
            ], 422);
        }

        $nada->delete();

        return response()->json([
            'message' => 'Nada deleted successfully',
        ]);
    }
}
