<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()->with('category:id,name')->orderBy('name')->paginate(10);

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.brands.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->where(fn ($query) => $query->where('category_id', $request->integer('category_id'))),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.unique' => 'Brand pada kategori ini sudah ada.',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('brands', 'public');
        }
        unset($validated['image']);

        Brand::create($validated);

        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil ditambahkan.');
    }

    public function edit(Brand $brand): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.brands.edit', compact('brand', 'categories'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')
                    ->ignore($brand->id)
                    ->where(fn ($query) => $query->where('category_id', $request->integer('category_id'))),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.unique' => 'Brand pada kategori ini sudah ada.',
        ]);

        if ($request->hasFile('image')) {
            if ($brand->image_path) {
                Storage::disk('public')->delete($brand->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('brands', 'public');
        }
        unset($validated['image']);

        $brand->update($validated);

        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil diperbarui.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $productCount = $brand->products()->count();
        if ($productCount > 0) {
            return redirect()
                ->route('admin.brands.index')
                ->with('error', "Brand tidak dapat dihapus karena masih digunakan oleh {$productCount} produk.");
        }

        if ($brand->image_path) {
            Storage::disk('public')->delete($brand->image_path);
        }
        try {
            $brand->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.brands.index')
                ->with('error', 'Brand tidak dapat dihapus karena masih terhubung dengan data lain.');
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil dihapus.');
    }
}
