<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $categories = $query->orderBy('sort_order')->orderBy('songcategoryname')->paginate(10)->withQueryString();

        $minSortOrder = Category::min('sort_order');
        $maxSortOrder = Category::max('sort_order');

        return view('admin.categories.index', compact('categories', 'minSortOrder', 'maxSortOrder'));
    }

    /**
     * Move the category up or down in the sort order.
     */
    public function move(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ]);

        $direction = $validated['direction'];
        $moved = false;

        DB::transaction(function () use ($category, $direction, &$moved) {
            $categories = Category::orderBy('sort_order')
                ->orderBy('songcategoryname')
                ->orderBy('songcategoryid')
                ->get()
                ->values();

            $index = $categories->search(
                fn (Category $item) => $item->songcategoryid === $category->songcategoryid
            );

            if ($index === false) {
                return;
            }

            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

            if ($targetIndex < 0 || $targetIndex >= $categories->count()) {
                return;
            }

            // Normalisasi dulu agar penukaran tetap benar walau ada sort_order duplikat.
            foreach ($categories as $position => $item) {
                $item->sort_order = $position + 1;
                $item->save();
            }

            $current = $categories[$index];
            $target = $categories[$targetIndex];

            $current->sort_order = $targetIndex + 1;
            $current->save();

            $target->sort_order = $index + 1;
            $target->save();

            $moved = true;
        });

        if (! $moved) {
            return back()->with('error', 'Kategori sudah berada di urutan paling '.($direction === 'up' ? 'atas' : 'bawah').'.');
        }

        return back()->with('success', 'Urutan kategori berhasil diperbarui.');
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
