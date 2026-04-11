<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MemberShowcase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MemberShowcaseController extends Controller
{
    public function edit(Request $request): View
    {
        abort_if($request->user()->isAdmin(), 403);

        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'image_path']);
        $selectedCategoryId = $request->integer('category_id') ?: (int) $categories->first()?->id;
        $brands = Brand::query()
            ->where('category_id', $selectedCategoryId)
            ->orderBy('name')
            ->get(['id', 'name', 'image_path']);
        $showcases = $request->user()
            ->memberShowcases()
            ->whereIn('brand_id', $brands->pluck('id'))
            ->get(['brand_id', 'showcase_number'])
            ->pluck('showcase_number', 'brand_id');

        return view('member.showcases.edit', compact('categories', 'selectedCategoryId', 'brands', 'showcases'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'exists:categories,id'],
            'showcases' => ['array'],
            'showcases.*' => ['nullable', 'regex:/^\d+$/', 'max:100'],
        ], [
            'showcases.*.regex' => 'Nomor etalase hanya boleh angka.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $categoryId = (int) $request->input('category_id');
            $brandIds = Brand::query()->where('category_id', $categoryId)->pluck('id');
            $payload = collect((array) $request->input('showcases', []));
            $normalized = $this->normalizedPayload($payload, $brandIds);
            $duplicates = $normalized->duplicates();

            if ($duplicates->isNotEmpty()) {
                $validator->errors()->add('showcases', 'Nomor etalase tidak boleh duplikat.');
            }

            $userId = $request->user()->id;
            foreach ($normalized as $brandId => $number) {
                $used = MemberShowcase::query()
                    ->where('user_id', $userId)
                    ->where('showcase_number', $number)
                    ->where('brand_id', '!=', $brandId)
                    ->exists();

                if ($used) {
                    $validator->errors()->add("showcases.$brandId", "Nomor etalase {$number} sudah dipakai.");
                }
            }
        });

        $validated = $validator->validate();

        $categoryId = (int) $validated['category_id'];
        $payload = $validated['showcases'] ?? [];
        $user = $request->user();
        $brandIds = Brand::query()
            ->where('category_id', $categoryId)
            ->pluck('id')
            ->all();
        $selectedIds = [];

        foreach ($brandIds as $brandId) {
            $number = trim((string) ($payload[$brandId] ?? ''));
            if ($number === '') {
                continue;
            }

            $selectedIds[] = $brandId;
            MemberShowcase::query()->updateOrCreate(
                ['user_id' => $user->id, 'brand_id' => $brandId],
                ['showcase_number' => $number]
            );
        }

        $user->memberShowcases()
            ->whereIn('brand_id', $brandIds)
            ->whereNotIn('brand_id', $selectedIds)
            ->delete();

        return redirect()->route('member.showcases.edit', ['category_id' => $categoryId])->with('success', 'Atur etalase berhasil disimpan.');
    }

    private function normalizedPayload(Collection $payload, Collection $brandIds): Collection
    {
        return $payload
            ->filter(fn ($value, $brandId) => $brandIds->contains((int) $brandId))
            ->mapWithKeys(fn ($value, $brandId) => [(int) $brandId => trim((string) $value)])
            ->filter(fn ($value) => $value !== '');
    }
}
