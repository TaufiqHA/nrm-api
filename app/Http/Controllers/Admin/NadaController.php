<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NadaController extends Controller
{
    /**
     * Display a listing of nadas.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Nada::withCount('songs');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nada', 'like', "%{$search}%");
        }

        $nadas = $query->orderBy('nada')->paginate(10)->withQueryString();

        if ($request->wantsJson() || ! view()->exists('admin.nadas.index')) {
            return response()->json($nadas);
        }

        return view('admin.nadas.index', compact('nadas'));
    }

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

        return redirect()->back(fallback: route('admin.nadas.index'))->with('success', 'Nada berhasil ditambahkan.');
    }

    /**
     * Display the specified nada.
     */
    public function show(Request $request, Nada $nada): View|JsonResponse
    {
        if ($request->wantsJson() || ! view()->exists('admin.nadas.show')) {
            return response()->json(['data' => $nada]);
        }

        return view('admin.nadas.show', compact('nada'));
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

        return redirect()->route('admin.nadas.index')->with('success', 'Nada berhasil diperbarui.');
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

        return redirect()->route('admin.nadas.index')->with('success', 'Nada berhasil dihapus.');
    }
}
