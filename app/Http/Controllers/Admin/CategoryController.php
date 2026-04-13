<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        }
        unset($validated['image']);

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        }
        unset($validated['image']);

        $category->update($validated);

        return $this->redirectToIndex($request)->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $productCount = $category->products()->count();
        $brandCount = $category->brands()->count();
        if ($productCount > 0 || $brandCount > 0) {
            return redirect()
                ->to($this->redirectPath($request))
                ->with('error', "Kategori tidak dapat dihapus karena masih digunakan oleh {$productCount} produk dan {$brandCount} brand.");
        }

        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }
        try {
            $category->delete();
        } catch (QueryException) {
            return redirect()
                ->to($this->redirectPath($request))
                ->with('error', 'Kategori tidak dapat dihapus karena masih terhubung dengan data lain.');
        }

        return $this->redirectToIndex($request)->with('success', 'Kategori berhasil dihapus.');
    }

    private function redirectToIndex(Request $request): RedirectResponse
    {
        return redirect()->to($this->redirectPath($request));
    }

    private function redirectPath(Request $request): string
    {
        $path = (string) $request->input('redirect_to', route('admin.categories.index'));

        return str_starts_with($path, '/') ? $path : route('admin.categories.index');
    }
}
