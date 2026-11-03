<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
        $validated = $this->validateCategory($request);

        $category = Category::create([
            'songcategoryname' => $validated['songcategoryname'],
        ]);

        $this->applyIcon($request, $category);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori lagu berhasil ditambahkan.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validateCategory($request, $category);

        $category->update([
            'songcategoryname' => $validated['songcategoryname'],
        ]);

        $this->applyIcon($request, $category);

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

        $this->deleteIconImage($category);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori lagu berhasil dihapus.');
    }

    /**
     * Validasi data kategori beserta pengaturan ikonnya.
     *
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, ?Category $category = null): array
    {
        $validated = $request->validate([
            'songcategoryname' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'songcategoryname')->ignore($category?->songcategoryid, 'songcategoryid'),
            ],
            'icon_source' => ['nullable', Rule::in(['auto', 'builtin', 'image'])],
            'icon' => ['nullable', 'string', Rule::in(array_keys(config('category_icons')))],
            'icon_file' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_icon' => ['nullable', 'boolean'],
        ], [
            'songcategoryname.required' => 'Nama kategori wajib diisi.',
            'songcategoryname.unique' => 'Nama kategori sudah digunakan.',
            'songcategoryname.max' => 'Nama kategori maksimal 255 karakter.',
            'icon.in' => 'Ikon bawaan yang dipilih tidak dikenali.',
            'icon_file.image' => 'Ikon kustom harus berupa file gambar.',
            'icon_file.mimes' => 'Ikon kustom harus berformat PNG, JPG, JPEG, atau WEBP.',
            'icon_file.max' => 'Ukuran ikon kustom maksimal 1 MB.',
        ]);

        $source = $validated['icon_source'] ?? 'auto';

        if ($source === 'builtin' && empty($validated['icon'])) {
            throw ValidationException::withMessages([
                'icon' => 'Pilih salah satu ikon bawaan terlebih dahulu.',
            ]);
        }

        if ($source === 'image'
            && ! $request->hasFile('icon_file')
            && empty($category?->icon_image)) {
            throw ValidationException::withMessages([
                'icon_file' => 'Unggah gambar ikon terlebih dahulu.',
            ]);
        }

        $validated['icon_source'] = $source;

        return $validated;
    }

    /**
     * Terapkan pengaturan ikon (otomatis, bawaan, atau gambar) ke kategori.
     */
    private function applyIcon(Request $request, Category $category): void
    {
        $source = $request->input('icon_source', 'auto');

        if ($source === 'builtin') {
            $this->deleteIconImage($category);
            $category->icon_image = null;
            $category->icon = $request->input('icon');
        } elseif ($source === 'image') {
            if ($request->hasFile('icon_file')) {
                $this->deleteIconImage($category);
                $category->icon_image = $request->file('icon_file')->store('category-icons', 'public');
            } elseif ($request->boolean('remove_icon')) {
                $this->deleteIconImage($category);
                $category->icon_image = null;
            }
            $category->icon = null;
        } else {
            $this->deleteIconImage($category);
            $category->icon_image = null;
            $category->icon = null;
        }

        $category->save();
    }

    /**
     * Hapus file gambar ikon lama dari disk publik.
     */
    private function deleteIconImage(Category $category): void
    {
        if (! empty($category->icon_image)
            && Storage::disk('public')->exists($category->icon_image)) {
            Storage::disk('public')->delete($category->icon_image);
        }
    }
}
