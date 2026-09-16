<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $query = Category::withCount('songs');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('songcategoryname', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('songcategoryname')->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'songcategoryname' => ['required', 'string', 'max:255', 'unique:categories,songcategoryname'],
        ], [
            'songcategoryname.required' => 'Nama kategori wajib diisi.',
            'songcategoryname.unique' => 'Nama kategori sudah digunakan.',
            'songcategoryname.max' => 'Nama kategori maksimal 255 karakter.',
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori lagu berhasil ditambahkan.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'songcategoryname' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'songcategoryname')->ignore($category->songcategoryid, 'songcategoryid'),
            ],
        ], [
            'songcategoryname.required' => 'Nama kategori wajib diisi.',
            'songcategoryname.unique' => 'Nama kategori sudah digunakan.',
            'songcategoryname.max' => 'Nama kategori maksimal 255 karakter.',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori lagu berhasil diperbarui.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->songs()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh beberapa lagu.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori lagu berhasil dihapus.');
    }
}
