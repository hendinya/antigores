<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $isAdmin = (bool) $request->user()?->isAdmin();
        $showcaseMap = $this->memberShowcaseMap($request);
        $products = $this->filteredQuery($request)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
        $products->setCollection($this->mapShowcaseForCollection($products->getCollection(), $showcaseMap, $isAdmin));

        return view('products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $isAdmin = (bool) $request->user()?->isAdmin();
        $showcaseMap = $this->memberShowcaseMap($request);
        $products = $this->filteredQuery($request)
            ->orderBy('name')
            ->limit(100)
            ->get();
        $products = $this->mapShowcaseForCollection($products, $showcaseMap, $isAdmin);

        return response()->json([
            'items' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name,
                'category_image' => $product->category->image_path ? asset('storage/'.$product->category->image_path) : null,
                'brand' => $product->brand->name,
                'brand_image' => $product->brand->image_path ? asset('storage/'.$product->brand->image_path) : null,
                'showcase' => $product->resolved_showcase,
                'antigores_size' => $product->phoneType->antigores_size,
                'camera_shape' => $product->phoneType->camera_shape,
            ])->values(),
            'count' => $products->count(),
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));
        if (! preg_match('/^\S+\s+\S/', $keyword)) {
            return response()->json(['items' => []]);
        }

        $categoryId = $request->integer('category_id');
        $brandId = $request->integer('brand_id');

        $items = Product::query()
            ->where('is_visible_for_affiliator', true)
            ->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->when($brandId, fn (Builder $query) => $query->where('brand_id', $brandId))
            ->where('name', 'like', "%{$keyword}%")
            ->selectRaw('MIN(name) as name, LOWER(TRIM(name)) as name_key')
            ->groupBy(DB::raw('LOWER(TRIM(name))'))
            ->orderBy('name')
            ->limit(10)
            ->pluck('name')
            ->unique(fn (string $name) => mb_strtolower(trim($name)))
            ->values();

        return response()->json(['items' => $items]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $categoryId = $request->integer('category_id');
        $brandId = $request->integer('brand_id');
        $keyword = trim((string) $request->string('keyword'));
        $exact = $request->boolean('exact');

        return Product::query()
            ->with(['category:id,name,image_path', 'brand:id,name,image_path', 'phoneType:id,name,antigores_size,camera_shape'])
            ->where('is_visible_for_affiliator', true)
            ->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->when($brandId, fn (Builder $query) => $query->where('brand_id', $brandId))
            ->when($keyword !== '' && $exact, fn (Builder $query) => $query->where('name', $keyword))
            ->when($keyword !== '' && ! $exact, fn (Builder $query) => $query->where(function (Builder $searchQuery) use ($keyword) {
                $searchQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhereHas('phoneType', fn (Builder $phoneTypeQuery) => $phoneTypeQuery->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$keyword}%"));
            }));
    }

    private function memberShowcaseMap(Request $request): Collection
    {
        $user = $request->user();
        if (! $user || $user->isAdmin()) {
            return collect();
        }

        return $user->memberShowcases()
            ->with('brand:id,name,category_id')
            ->get()
            ->mapWithKeys(function ($memberShowcase) {
                $brand = $memberShowcase->brand;
                if (! $brand) {
                    return [];
                }
                $key = $brand->category_id.'|'.mb_strtolower(trim((string) $brand->name));

                return [$key => (string) $memberShowcase->showcase_number];
            });
    }

    private function mapShowcaseForCollection(EloquentCollection $products, Collection $showcaseMap, bool $isAdmin): EloquentCollection
    {
        return $products->map(function (Product $product) use ($showcaseMap, $isAdmin) {
            $lookupKey = $product->category_id.'|'.mb_strtolower(trim((string) $product->brand->name));
            $mappedShowcase = trim((string) $showcaseMap->get($lookupKey, ''));
            $fallback = $isAdmin ? $product->phoneType->name : 'Etalase belum di atur';
            $product->setAttribute('resolved_showcase', $mappedShowcase !== '' ? $mappedShowcase : $fallback);

            return $product;
        });
    }
}
