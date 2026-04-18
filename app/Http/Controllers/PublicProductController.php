<?php

namespace App\Http\Controllers;

use App\Models\PhoneType;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicProductController extends Controller
{
    public function index(Request $request): View
    {
        $payload = $this->searchPayload($request->merge(['page' => $request->integer('page', 1)]));

        return view('public.products.index', [
            'initialItems' => $payload['items'],
            'initialPagination' => $payload['pagination'],
            'initialKeyword' => trim((string) $request->string('keyword')),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        return response()->json($this->searchPayload($request));
    }

    public function suggest(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));
        if (mb_strlen($keyword) < 2) {
            return response()->json(['items' => []]);
        }

        $cacheKey = 'public_products_suggest:v2:'.md5($keyword);
        $items = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($keyword) {
            return Product::query()
                ->where('name', 'like', "%{$keyword}%")
                ->selectRaw('MIN(name) as name, LOWER(TRIM(name)) as name_key')
                ->groupBy(DB::raw('LOWER(TRIM(name))'))
                ->orderBy('name')
                ->limit(8)
                ->pluck('name')
                ->unique(fn (string $name) => mb_strtolower(trim($name)))
                ->values()
                ->all();
        });

        return response()->json(['items' => $items]);
    }

    private function searchPayload(Request $request): array
    {
        $keyword = trim((string) $request->string('keyword'));
        $exact = $request->boolean('exact');
        $size = trim((string) $request->string('size'));
        $page = max(1, $request->integer('page', 1));
        $perPage = 16;

        $cacheKey = implode(':', [
            'public_products',
            md5($keyword),
            $exact ? 'exact' : 'fuzzy',
            md5($size),
            $page,
            $perPage,
        ]);

        return Cache::remember($cacheKey, now()->addSeconds(60), function () use ($keyword, $exact, $size, $page, $perPage) {
            $products = Product::query()
                ->with(['category:id,name,image_path', 'brand:id,name,image_path', 'phoneType:id,name,antigores_size,camera_shape'])
                ->when($keyword !== '' && $exact, fn ($query) => $query->whereRaw('LOWER(name) = ?', [mb_strtolower($keyword)]))
                ->when($keyword !== '' && ! $exact, fn ($query) => $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhereHas('phoneType', fn ($phoneTypeQuery) => $phoneTypeQuery
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere('antigores_size', 'like', "%{$keyword}%")
                            ->orWhere('camera_shape', 'like', "%{$keyword}%"));
                }))
                ->orderBy('name')
                ->paginate($perPage, ['*'], 'page', $page);

            $showcases = PhoneType::query()
                ->when($size !== '', fn ($query) => $query->where('antigores_size', 'like', "%{$size}%"))
                ->orderBy('name')
                ->limit(50)
                ->get(['name', 'antigores_size', 'camera_shape']);

            return [
                'items' => $products->getCollection()->map(function (Product $product) {
                    $imagePath = $product->category->image_path ?: $product->brand->image_path;

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'showcase' => $product->phoneType->name,
                        'category' => $product->category->name,
                        'antigores_size' => $product->phoneType->antigores_size,
                        'camera_shape' => $product->phoneType->camera_shape,
                        'image_url' => $imagePath ? asset('storage/'.$imagePath) : null,
                    ];
                })->values()->all(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'has_more' => $products->hasMorePages(),
                    'total' => $products->total(),
                ],
                'showcases' => $showcases->map(fn (PhoneType $phoneType) => [
                    'name' => $phoneType->name,
                    'antigores_size' => $phoneType->antigores_size,
                    'camera_shape' => $phoneType->camera_shape,
                ])->values()->all(),
            ];
        });
    }
}
