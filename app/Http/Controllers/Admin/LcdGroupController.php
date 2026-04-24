<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LcdGroup;
use App\Models\ProductMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LcdGroupController extends Controller
{
    public function index(): View
    {
        $groups = LcdGroup::query()
            ->withCount('productMasters')
            ->with(['productMasters' => fn ($query) => $query->with('brand:id,name')->orderBy('name')])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.lcd-groups.index', compact('groups'));
    }

    public function create(): View
    {
        return view('admin.lcd-groups.create', [
            'productMasters' => $this->productMasterOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:lcd_groups,name'],
            'note' => ['nullable', 'string', 'max:1000'],
            'product_master_ids' => ['required', 'array', 'min:1'],
            'product_master_ids.*' => ['integer', 'exists:product_masters,id'],
        ]);

        $group = LcdGroup::query()->create([
            'name' => trim((string) $validated['name']),
            'note' => $validated['note'] ?? null,
        ]);
        $group->productMasters()->sync(collect($validated['product_master_ids'])->unique()->values()->all());

        return redirect()->route('admin.lcd-groups.index')->with('success', 'Grup LCD berhasil ditambahkan.');
    }

    public function edit(LcdGroup $lcdGroup): View
    {
        $lcdGroup->load('productMasters:id');

        return view('admin.lcd-groups.edit', [
            'lcdGroup' => $lcdGroup,
            'productMasters' => $this->productMasterOptions(),
        ]);
    }

    public function update(Request $request, LcdGroup $lcdGroup): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('lcd_groups', 'name')->ignore($lcdGroup->id)],
            'note' => ['nullable', 'string', 'max:1000'],
            'product_master_ids' => ['required', 'array', 'min:1'],
            'product_master_ids.*' => ['integer', 'exists:product_masters,id'],
        ]);

        $lcdGroup->update([
            'name' => trim((string) $validated['name']),
            'note' => $validated['note'] ?? null,
        ]);
        $lcdGroup->productMasters()->sync(collect($validated['product_master_ids'])->unique()->values()->all());

        return $this->redirectToIndex($request)->with('success', 'Grup LCD berhasil diperbarui.');
    }

    public function destroy(Request $request, LcdGroup $lcdGroup): RedirectResponse
    {
        $lcdGroup->delete();

        return $this->redirectToIndex($request)->with('success', 'Grup LCD berhasil dihapus.');
    }

    private function productMasterOptions()
    {
        return ProductMaster::query()
            ->with(['brand:id,name', 'variants.phoneType:id,name'])
            ->orderBy('name')
            ->get()
            ->map(function (ProductMaster $master): array {
                $showcases = $master->variants
                    ->pluck('phoneType.name')
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode(', ');

                $label = trim($master->name)
                    .' | '.($master->brand->name ?? '-')
                    .' | '.($showcases !== '' ? Str::limit($showcases, 55) : '-');

                return [
                    'id' => $master->id,
                    'label' => $label,
                ];
            })
            ->values();
    }

    private function redirectToIndex(Request $request): RedirectResponse
    {
        return redirect()->to($this->redirectPath($request));
    }

    private function redirectPath(Request $request): string
    {
        $path = (string) $request->input('redirect_to', route('admin.lcd-groups.index'));
        if (str_starts_with($path, '/')) {
            return $path;
        }
        $parsed = parse_url($path);
        if (is_array($parsed) && isset($parsed['path']) && str_starts_with((string) $parsed['path'], '/')) {
            return $parsed['path'].(isset($parsed['query']) ? '?'.$parsed['query'] : '');
        }

        return route('admin.lcd-groups.index');
    }
}
