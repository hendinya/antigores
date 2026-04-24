<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LcdGroup;
use App\Models\ProductMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LcdGroupController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->string('keyword'));

        $groups = LcdGroup::query()
            ->withCount('productMasters')
            ->with(['productMasters' => fn ($query) => $query->with('brand:id,name')->orderBy('name')])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('note', 'like', "%{$keyword}%")
                        ->orWhereHas('productMasters', fn ($masterQuery) => $masterQuery->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.lcd-groups.index', compact('groups', 'keyword'));
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
        $productMasterIds = collect($validated['product_master_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();
        $this->ensureProductMastersAvailable($productMasterIds);

        $group = LcdGroup::query()->create([
            'name' => trim((string) $validated['name']),
            'note' => $validated['note'] ?? null,
        ]);
        $group->productMasters()->sync($productMasterIds);

        return redirect()->route('admin.lcd-groups.index')->with('success', 'Grup LCD berhasil ditambahkan.');
    }

    public function edit(LcdGroup $lcd_group): View
    {
        $lcd_group->load('productMasters:id');

        return view('admin.lcd-groups.edit', [
            'lcdGroup' => $lcd_group,
            'productMasters' => $this->productMasterOptions($lcd_group->id),
        ]);
    }

    public function update(Request $request, LcdGroup $lcd_group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('lcd_groups', 'name')->ignore($lcd_group->id)],
            'note' => ['nullable', 'string', 'max:1000'],
            'product_master_ids' => ['required', 'array', 'min:1'],
            'product_master_ids.*' => ['integer', 'exists:product_masters,id'],
        ]);
        $productMasterIds = collect($validated['product_master_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();
        $this->ensureProductMastersAvailable($productMasterIds, $lcd_group->id);

        $lcd_group->update([
            'name' => trim((string) $validated['name']),
            'note' => $validated['note'] ?? null,
        ]);
        $lcd_group->productMasters()->sync($productMasterIds);

        return $this->redirectToIndex($request)->with('success', 'Grup LCD berhasil diperbarui.');
    }

    public function destroy(Request $request, LcdGroup $lcd_group): RedirectResponse
    {
        $lcd_group->delete();

        return $this->redirectToIndex($request)->with('success', 'Grup LCD berhasil dihapus.');
    }

    private function productMasterOptions(?int $editingGroupId = null)
    {
        $assignedMasterIds = DB::table('lcd_group_product_master')
            ->when($editingGroupId, fn ($query) => $query->where('lcd_group_id', '!=', $editingGroupId))
            ->pluck('product_master_id')
            ->unique()
            ->values();

        return ProductMaster::query()
            ->with(['brand:id,name', 'variants.phoneType:id,name'])
            ->whereNotIn('id', $assignedMasterIds->all())
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

    private function ensureProductMastersAvailable(array $productMasterIds, ?int $ignoreGroupId = null): void
    {
        $usedByOtherGroup = DB::table('lcd_group_product_master')
            ->join('lcd_groups', 'lcd_groups.id', '=', 'lcd_group_product_master.lcd_group_id')
            ->join('product_masters', 'product_masters.id', '=', 'lcd_group_product_master.product_master_id')
            ->whereIn('lcd_group_product_master.product_master_id', $productMasterIds)
            ->when($ignoreGroupId, fn ($query) => $query->where('lcd_group_product_master.lcd_group_id', '!=', $ignoreGroupId))
            ->select('product_masters.name as product_name', 'lcd_groups.name as group_name')
            ->get();

        if ($usedByOtherGroup->isEmpty()) {
            return;
        }

        $message = $usedByOtherGroup
            ->map(fn ($row) => trim((string) $row->product_name).' sudah ada di grup "'.trim((string) $row->group_name).'"')
            ->implode('; ');

        throw ValidationException::withMessages([
            'product_master_ids' => $message,
        ]);
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
