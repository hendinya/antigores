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
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.lcd-groups.index', compact('groups', 'keyword'));
    }

    public function create(): View
    {
        $productMasterOptions = $this->productMasterOptions();

        return view('admin.lcd-groups.create', [
            'productMasterOptions' => $productMasterOptions,
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
        $selectedOptionIds = collect($validated['product_master_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();
        $productMasterIds = $this->expandProductMasterIdsFromOptionIds($selectedOptionIds);
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
        $lcd_group->load('productMasters:id,name,brand_id');
        $productMasterOptions = $this->productMasterOptions($lcd_group->id);
        $selectedOptionIds = $this->selectedOptionIdsForGroup($lcd_group, $productMasterOptions);

        return view('admin.lcd-groups.edit', [
            'lcdGroup' => $lcd_group,
            'productMasterOptions' => $productMasterOptions,
            'selectedOptionIds' => $selectedOptionIds,
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
        $selectedOptionIds = collect($validated['product_master_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();
        $productMasterIds = $this->expandProductMasterIdsFromOptionIds($selectedOptionIds);
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
        $assignmentRows = DB::table('lcd_group_product_master')
            ->join('lcd_groups', 'lcd_groups.id', '=', 'lcd_group_product_master.lcd_group_id')
            ->select(
                'lcd_group_product_master.product_master_id as product_master_id',
                'lcd_group_product_master.lcd_group_id as lcd_group_id',
                'lcd_groups.name as lcd_group_name'
            )
            ->get();
        $assignmentMap = $assignmentRows->groupBy('product_master_id');

        return ProductMaster::query()
            ->with('brand:id,name')
            ->whereHas('variants')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (ProductMaster $master) => $this->masterOptionKey($master->name, (int) $master->brand_id))
            ->map(function ($groupedMasters) use ($assignmentMap, $editingGroupId): array {
                /** @var \Illuminate\Support\Collection<int, ProductMaster> $groupedMasters */
                $representative = $groupedMasters->sortByDesc('id')->first();
                $masterIds = $groupedMasters->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                $assignedGroupNames = collect($masterIds)
                    ->flatMap(fn (int $masterId) => collect($assignmentMap->get($masterId, [])))
                    ->filter(fn ($row) => $editingGroupId === null || (int) $row->lcd_group_id !== $editingGroupId)
                    ->pluck('lcd_group_name')
                    ->filter()
                    ->map(fn ($name) => trim((string) $name))
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'id' => (int) $representative->id,
                    'name' => trim((string) $representative->name),
                    'master_ids' => $masterIds,
                    'key' => $this->masterOptionKey((string) $representative->name, (int) $representative->brand_id),
                    'disabled' => ! empty($assignedGroupNames),
                    'assigned_group_names' => $assignedGroupNames,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function masterOptionKey(string $name, int $brandId): string
    {
        return Str::lower(trim($name)).'|'.$brandId;
    }

    private function expandProductMasterIdsFromOptionIds(array $selectedOptionIds): array
    {
        if (empty($selectedOptionIds)) {
            return [];
        }

        $selectedMasters = ProductMaster::query()
            ->whereIn('id', $selectedOptionIds)
            ->get(['id', 'name', 'brand_id']);
        if ($selectedMasters->isEmpty()) {
            return [];
        }

        $selectedKeys = $selectedMasters
            ->map(fn (ProductMaster $master) => $this->masterOptionKey((string) $master->name, (int) $master->brand_id))
            ->unique()
            ->values();
        $brandIds = $selectedMasters->pluck('brand_id')->unique()->values()->all();

        return ProductMaster::query()
            ->whereIn('brand_id', $brandIds)
            ->whereHas('variants')
            ->get(['id', 'name', 'brand_id'])
            ->filter(fn (ProductMaster $master) => $selectedKeys->contains($this->masterOptionKey((string) $master->name, (int) $master->brand_id)))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function selectedOptionIdsForGroup(LcdGroup $lcdGroup, $productMasterOptions): array
    {
        $selectedKeys = $lcdGroup->productMasters
            ->map(fn (ProductMaster $master) => $this->masterOptionKey((string) $master->name, (int) $master->brand_id))
            ->unique()
            ->values();

        return collect($productMasterOptions)
            ->filter(fn (array $option) => $selectedKeys->contains((string) ($option['key'] ?? '')))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
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
