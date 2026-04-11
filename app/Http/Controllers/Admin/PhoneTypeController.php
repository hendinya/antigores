<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhoneType;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneTypeController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->string('keyword'));
        $phoneTypes = PhoneType::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('antigores_size', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.phone-types.index', compact('phoneTypes'));
    }

    public function create(): View
    {
        return view('admin.phone-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'antigores_size' => ['required', 'string', 'max:100'],
            'camera_shape' => ['required', 'string', 'max:100'],
            'shopping_link' => ['nullable', 'url', 'max:500'],
            'masteran' => ['nullable', 'string', 'max:1000'],
        ]);

        PhoneType::create($validated);

        return redirect()->route('admin.phone-types.index')->with('success', 'Etalase berhasil ditambahkan.');
    }

    public function edit(PhoneType $phoneType): View
    {
        return view('admin.phone-types.edit', compact('phoneType'));
    }

    public function update(Request $request, PhoneType $phoneType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'antigores_size' => ['required', 'string', 'max:100'],
            'camera_shape' => ['required', 'string', 'max:100'],
            'shopping_link' => ['nullable', 'url', 'max:500'],
            'masteran' => ['nullable', 'string', 'max:1000'],
        ]);

        $phoneType->update($validated);

        return redirect()->route('admin.phone-types.index')->with('success', 'Etalase berhasil diperbarui.');
    }

    public function destroy(PhoneType $phoneType): RedirectResponse
    {
        $productCount = $phoneType->products()->count();
        if ($productCount > 0) {
            return redirect()
                ->route('admin.phone-types.index')
                ->with('error', "Etalase tidak dapat dihapus karena masih digunakan oleh {$productCount} produk.");
        }

        try {
            $phoneType->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.phone-types.index')
                ->with('error', 'Etalase tidak dapat dihapus karena masih terhubung dengan data lain.');
        }

        return redirect()->route('admin.phone-types.index')->with('success', 'Etalase berhasil dihapus.');
    }
}
