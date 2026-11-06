<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NadaController extends Controller
{
    /**
     * Store a newly created nada in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nada' => ['required', 'string', 'max:50', 'unique:nadas,nada'],
        ], [
            'nada.required' => 'Nama nada wajib diisi.',
            'nada.unique' => 'Nama nada sudah digunakan.',
            'nada.max' => 'Nama nada maksimal 50 karakter.',
        ]);

        $nada = Nada::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Nada berhasil ditambahkan.',
                'data' => $nada,
            ], 201);
        }

        return back()->with('success', 'Nada berhasil ditambahkan.');
    }

    /**
     * Update the specified nada in storage.
     */
    public function update(Request $request, Nada $nada): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nada' => [
                'required',
                'string',
                'max:50',
                Rule::unique('nadas', 'nada')->ignore($nada->id),
            ],
        ], [
            'nada.required' => 'Nama nada wajib diisi.',
            'nada.unique' => 'Nama nada sudah digunakan.',
            'nada.max' => 'Nama nada maksimal 50 karakter.',
        ]);

        $nada->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Nada berhasil diperbarui.',
                'data' => $nada->fresh(),
            ]);
        }

        return back()->with('success', 'Nada berhasil diperbarui.');
    }

    /**
     * Remove the specified nada from storage.
     */
    public function destroy(Request $request, Nada $nada): RedirectResponse|JsonResponse
    {
        if ($nada->songs()->exists()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Nada tidak dapat dihapus karena masih digunakan oleh beberapa lagu.',
                ], 422);
            }

            return back()->with('error', 'Nada tidak dapat dihapus karena masih digunakan oleh beberapa lagu.');
        }

        $nada->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Nada berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Nada berhasil dihapus.');
    }
}
