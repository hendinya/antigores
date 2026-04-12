<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()
            ->whereNotNull('category_id')
            ->with('category:id,name')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $masterBrands = Brand::query()
            ->whereNull('category_id')
            ->orderBy('name')
            ->get(['id', 'name', 'image_path']);

        return view('admin.brands.create', compact('categories', 'masterBrands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'master_brand_id' => ['required', Rule::exists('brands', 'id')->where(fn ($query) => $query->whereNull('category_id'))],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $masterBrand = Brand::query()->whereNull('category_id')->findOrFail((int) $validated['master_brand_id']);
        $name = $masterBrand->name;
        $categoryId = (int) $validated['category_id'];
        $exists = Brand::query()
            ->where('name', $name)
            ->where('category_id', $categoryId)
            ->exists();
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['master_brand_id' => 'Brand pada kategori ini sudah ada.']);
        }

        Brand::create([
            'name' => $name,
            'category_id' => $categoryId,
            'image_path' => $masterBrand->image_path,
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil ditambahkan.');
    }

    public function edit(Brand $brand): View
    {
        if ($brand->category_id === null) {
            redirect()->route('admin.master-brands.edit', $brand)->with('success', 'Data ini adalah Master Brand, diarahkan ke halaman yang sesuai.')->throwResponse();
        }

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $masterBrands = Brand::query()
            ->whereNull('category_id')
            ->orderBy('name')
            ->get(['id', 'name', 'image_path']);
        $selectedMasterBrandId = $masterBrands->firstWhere('name', $brand->name)?->id;

        return view('admin.brands.edit', compact('brand', 'categories', 'masterBrands', 'selectedMasterBrandId'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        if ($brand->category_id === null) {
            return redirect()
                ->route('admin.master-brands.edit', $brand)
                ->with('error', 'Perubahan untuk data ini dilakukan dari halaman Master Brands.');
        }

        $validated = $request->validate([
            'master_brand_id' => ['required', Rule::exists('brands', 'id')->where(fn ($query) => $query->whereNull('category_id'))],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $masterBrand = Brand::query()->whereNull('category_id')->findOrFail((int) $validated['master_brand_id']);
        $name = $masterBrand->name;
        $categoryId = (int) $validated['category_id'];
        $exists = Brand::query()
            ->where('name', $name)
            ->where('category_id', $categoryId)
            ->where('id', '!=', $brand->id)
            ->exists();
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['master_brand_id' => 'Brand pada kategori ini sudah ada.']);
        }

        $brand->update([
            'name' => $name,
            'category_id' => $categoryId,
            'image_path' => $masterBrand->image_path,
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil diperbarui.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->category_id === null) {
            return redirect()
                ->route('admin.master-brands.index')
                ->with('error', 'Data ini adalah Master Brand. Hapus dari halaman Master Brands.');
        }

        $productCount = $brand->products()->count();
        if ($productCount > 0) {
            return redirect()
                ->route('admin.brands.index')
                ->with('error', "Brand tidak dapat dihapus karena masih digunakan oleh {$productCount} produk.");
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
