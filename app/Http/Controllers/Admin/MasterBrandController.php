<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterBrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()
            ->whereNull('category_id')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.master-brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.master-brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->where(fn ($query) => $query->whereNull('category_id')),
            ],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.unique' => 'Master brand sudah ada.',
        ]);

        $validated['category_id'] = null;
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('brands', 'public');
        }
        unset($validated['image']);

        Brand::create($validated);

        return redirect()->route('admin.master-brands.index')->with('success', 'Master brand berhasil ditambahkan.');
    }

    public function edit(Brand $master_brand): View
    {
        abort_if($master_brand->category_id !== null, 404);

        return view('admin.master-brands.edit', ['brand' => $master_brand]);
    }

    public function update(Request $request, Brand $master_brand): RedirectResponse
    {
        abort_if($master_brand->category_id !== null, 404);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')
                    ->ignore($master_brand->id)
                    ->where(fn ($query) => $query->whereNull('category_id')),
            ],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.unique' => 'Master brand sudah ada.',
        ]);

        $validated['category_id'] = null;
        if ($request->hasFile('image')) {
            if ($master_brand->image_path) {
                Storage::disk('public')->delete($master_brand->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('brands', 'public');
        }
        unset($validated['image']);

        $master_brand->update($validated);

        return $this->redirectToIndex($request)->with('success', 'Master brand berhasil diperbarui.');
    }

    public function destroy(Brand $master_brand): RedirectResponse
    {
        abort_if($master_brand->category_id !== null, 404);

        $productCount = $master_brand->products()->count();
        if ($productCount > 0) {
            return redirect()
                ->to($this->redirectPath($request))
                ->with('error', "Master brand tidak dapat dihapus karena masih digunakan oleh {$productCount} produk.");
        }

        if ($master_brand->image_path) {
            Storage::disk('public')->delete($master_brand->image_path);
        }
        try {
            $master_brand->delete();
        } catch (QueryException) {
            return redirect()
                ->to($this->redirectPath($request))
                ->with('error', 'Master brand tidak dapat dihapus karena masih terhubung dengan data lain.');
        }

        return $this->redirectToIndex($request)->with('success', 'Master brand berhasil dihapus.');
    }

    private function redirectToIndex(Request $request): RedirectResponse
    {
        return redirect()->to($this->redirectPath($request));
    }

    private function redirectPath(Request $request): string
    {
        $path = (string) $request->input('redirect_to', route('admin.master-brands.index'));

        return str_starts_with($path, '/') ? $path : route('admin.master-brands.index');
    }
}
