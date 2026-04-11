<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PhoneType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    private const IMPORT_PREVIEW_SESSION_KEY = 'admin_products_import_preview_rows';

    public function index(Request $request): View
    {
        $products = $this->filteredQuery($request)
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->whereNull('category_id')->orderBy('name')->get(['id', 'name']),
            'phoneTypes' => PhoneType::query()->orderBy('name')->get(['id', 'name']),
            'importPreview' => Session::get(self::IMPORT_PREVIEW_SESSION_KEY),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $products = $this->filteredQuery($request)
            ->paginate(10);

        return response()->json([
            'items' => $products->getCollection()->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'product_note' => e(Str::limit((string) $product->product_note, 80)),
                'is_visible_for_affiliator' => $product->is_visible_for_affiliator,
                'category' => $product->category->name,
                'category_image' => $product->category->image_path ? asset('storage/'.$product->category->image_path) : null,
                'brand' => $product->brand->name,
                'brand_image' => $product->brand->image_path ? asset('storage/'.$product->brand->image_path) : null,
                'showcase' => $product->phoneType->name,
                'antigores_size' => $product->phoneType->antigores_size,
                'camera_shape' => $product->phoneType->camera_shape,
                'edit_url' => route('admin.products.edit', $product),
                'delete_url' => route('admin.products.destroy', $product),
                'visibility_url' => route('admin.products.visibility', $product),
            ])->values(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->whereNull('category_id')->orderBy('name')->get(['id', 'name']),
            'phoneTypes' => PhoneType::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->where(
                    fn ($query) => $query
                        ->where('category_id', $request->integer('category_id'))
                        ->where('brand_id', $request->integer('brand_id'))
                ),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', Rule::exists('brands', 'id')->where(fn ($query) => $query->whereNull('category_id'))],
            'phone_type_id' => ['required', 'exists:phone_types,id'],
            'product_note' => ['nullable', 'string', 'max:1000'],
            'is_visible_for_affiliator' => ['nullable', 'boolean'],
        ], [
            'name.unique' => 'Nama produk dengan kategori dan brand ini sudah ada.',
            'brand_id.exists' => 'Brand harus berasal dari Master Brands.',
        ]);
        $validated['is_visible_for_affiliator'] = (bool) ($validated['is_visible_for_affiliator'] ?? true);

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->whereNull('category_id')->orderBy('name')->get(['id', 'name']),
            'phoneTypes' => PhoneType::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')
                    ->ignore($product->id)
                    ->where(
                        fn ($query) => $query
                            ->where('category_id', $request->integer('category_id'))
                            ->where('brand_id', $request->integer('brand_id'))
                    ),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', Rule::exists('brands', 'id')->where(fn ($query) => $query->whereNull('category_id'))],
            'phone_type_id' => ['required', 'exists:phone_types,id'],
            'product_note' => ['nullable', 'string', 'max:1000'],
            'is_visible_for_affiliator' => ['nullable', 'boolean'],
        ], [
            'name.unique' => 'Nama produk dengan kategori dan brand ini sudah ada.',
            'brand_id.exists' => 'Brand harus berasal dari Master Brands.',
        ]);
        $validated['is_visible_for_affiliator'] = (bool) ($validated['is_visible_for_affiliator'] ?? false);

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function updateVisibility(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'is_visible_for_affiliator' => ['required', 'boolean'],
        ]);

        $isVisible = (bool) $validated['is_visible_for_affiliator'];
        $product->update(['is_visible_for_affiliator' => $isVisible]);

        return redirect()->route('admin.products.index')->with(
            'success',
            $isVisible
                ? 'Produk ditampilkan untuk affiliator.'
                : 'Produk disembunyikan dari affiliator.'
        );
    }

    public function import(Request $request): RedirectResponse
    {
        if ($request->boolean('commit_preview')) {
            $rows = collect(Session::pull(self::IMPORT_PREVIEW_SESSION_KEY, []));
            if ($rows->isEmpty()) {
                return redirect()->route('admin.products.index')->with('error', 'Tidak ada data preview untuk disimpan.');
            }

            $created = 0;
            $skipped = 0;
            DB::beginTransaction();
            try {
                $rows->each(function (array $row) use (&$created, &$skipped) {
                    $exists = Product::query()
                        ->where('name', $row['name'])
                        ->where('category_id', $row['category_id'])
                        ->where('brand_id', $row['brand_id'])
                        ->exists();
                    if ($exists) {
                        $skipped++;

                        return;
                    }

                    Product::query()->create([
                        'name' => $row['name'],
                        'category_id' => $row['category_id'],
                        'brand_id' => $row['brand_id'],
                        'phone_type_id' => $row['phone_type_id'],
                    ]);
                    $created++;
                });
                DB::commit();
            } catch (\Throwable $throwable) {
                DB::rollBack();

                return redirect()->route('admin.products.index')->with('error', 'Simpan hasil preview gagal diproses.');
            }

            return redirect()->route('admin.products.index')->with('success', "Data preview berhasil disimpan. Berhasil: {$created}, dilewati: {$skipped}.");
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        $result = $this->parseImportFile($validated['file']->getRealPath());
        if ($request->boolean('preview')) {
            Session::put(self::IMPORT_PREVIEW_SESSION_KEY, $result['to_insert']);

            return redirect()->route('admin.products.index')->with('success', "Preview siap. Valid: {$result['valid_count']}, duplikat: {$result['duplicate_count']}, error: {$result['error_count']}.")
                ->with('import_preview_rows', $result['preview_rows']);
        }

        DB::beginTransaction();
        try {
            foreach ($result['to_insert'] as $row) {
                Product::query()->create($row);
            }
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();

            return redirect()->route('admin.products.index')->with('error', 'Import gagal diproses.');
        }

        return redirect()->route('admin.products.index')->with(
            'success',
            "Import selesai. Berhasil: {$result['valid_count']}, dilewati (duplikat): {$result['duplicate_count']}, error: {$result['error_count']}."
        )->with('import_preview_rows', $result['preview_rows']);
    }

    public function template(): BinaryFileResponse
    {
        $path = storage_path('app/template-import-produk.xlsx');
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['nama_produk', 'kategori', 'brand', 'etalase']));
        $writer->addRow(Row::fromValues(['Antigores Redmi 15C', 'Kaca Bening', 'Xiaomi', '55']));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['Referensi Kategori']));
        foreach (Category::query()->orderBy('name')->pluck('name') as $name) {
            $writer->addRow(Row::fromValues([$name]));
        }
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['Referensi Brand']));
        foreach (Brand::query()->whereNull('category_id')->orderBy('name')->pluck('name') as $name) {
            $writer->addRow(Row::fromValues([$name]));
        }
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['Referensi Etalase']));
        foreach (PhoneType::query()->orderBy('name')->pluck('name') as $name) {
            $writer->addRow(Row::fromValues([$name]));
        }
        $writer->close();

        return response()->download(
            $path,
            'template-import-produk.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function parseImportFile(string $path): array
    {
        $reader = new XlsxReader;
        $reader->open($path);

        $toInsert = [];
        $previewRows = [];
        $line = 1;
        $isHeader = true;
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $line++;
                    $cells = $row->toArray();
                    if ($isHeader) {
                        $isHeader = false;

                        continue;
                    }

                    $name = trim((string) ($cells[0] ?? ''));
                    $categoryName = trim((string) ($cells[1] ?? ''));
                    $brandName = trim((string) ($cells[2] ?? ''));
                    $showcaseName = trim((string) ($cells[3] ?? ''));

                    if ($name === '' && $categoryName === '' && $brandName === '' && $showcaseName === '') {
                        continue;
                    }

                    $rowPreview = [
                        'line' => $line,
                        'name' => $name,
                        'category' => $categoryName,
                        'brand' => $brandName,
                        'showcase' => $showcaseName,
                        'status' => 'error',
                        'message' => '',
                    ];

                    if ($name === '' || $categoryName === '' || $brandName === '' || $showcaseName === '') {
                        $rowPreview['message'] = 'Semua kolom wajib diisi.';
                        $previewRows[] = $rowPreview;
                        $errorCount++;

                        continue;
                    }

                    $category = Category::query()->whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])->first();
                    $notes = [];
                    if (! $category) {
                        $category = Category::query()->create(['name' => $categoryName]);
                        $notes[] = "Kategori '{$categoryName}' dibuat otomatis";
                    }

                    $brand = Brand::query()
                        ->whereNull('category_id')
                        ->whereRaw('LOWER(name) = ?', [Str::lower($brandName)])
                        ->first();
                    if (! $brand) {
                        $brand = Brand::query()->create([
                            'name' => $brandName,
                            'category_id' => null,
                        ]);
                        $notes[] = "Master brand '{$brandName}' dibuat otomatis";
                    }

                    $phoneType = PhoneType::query()->whereRaw('LOWER(name) = ?', [Str::lower($showcaseName)])->first();
                    if (! $phoneType) {
                        $phoneType = PhoneType::query()->create(['name' => $showcaseName]);
                        $notes[] = "Etalase '{$showcaseName}' dibuat otomatis";
                    }

                    $exists = Product::query()
                        ->where('name', $name)
                        ->where('category_id', $category->id)
                        ->where('brand_id', $brand->id)
                        ->exists();
                    if ($exists) {
                        $rowPreview['status'] = 'duplicate';
                        $rowPreview['message'] = 'Duplikat: nama produk dengan kategori dan brand ini sudah ada.';
                        $previewRows[] = $rowPreview;
                        $duplicateCount++;

                        continue;
                    }

                    $rowPreview['status'] = 'valid';
                    $rowPreview['message'] = $notes === [] ? 'Siap diimport.' : 'Siap diimport. '.implode('. ', $notes).'.';
                    $previewRows[] = $rowPreview;
                    $toInsert[] = [
                        'name' => $name,
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'phone_type_id' => $phoneType->id,
                    ];
                    $validCount++;
                }
                break;
            }
        } finally {
            $reader->close();
        }

        return [
            'to_insert' => $toInsert,
            'preview_rows' => $previewRows,
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
        ];
    }

    private function filteredQuery(Request $request): Builder
    {
        $keyword = trim((string) $request->string('keyword'));
        $categoryId = $request->integer('category_id');
        $brandId = $request->integer('brand_id');
        $phoneTypeId = $request->integer('phone_type_id');

        return Product::query()
            ->with(['category:id,name,image_path', 'brand:id,name,image_path', 'phoneType:id,name,antigores_size,camera_shape'])
            ->when($keyword !== '', fn ($query) => $query->where(function ($searchQuery) use ($keyword) {
                $searchQuery
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('product_note', 'like', "%{$keyword}%")
                    ->orWhereHas('phoneType', fn ($phoneTypeQuery) => $phoneTypeQuery->where('antigores_size', 'like', "%{$keyword}%"));
            }))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($brandId, fn ($query) => $query->where('brand_id', $brandId))
            ->when($phoneTypeId, fn ($query) => $query->where('phone_type_id', $phoneTypeId))
            ->orderBy('name');
    }
}
