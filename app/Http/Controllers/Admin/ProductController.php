<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PhoneType;
use App\Models\Product;
use App\Models\ProductMaster;
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
            'items' => $products->getCollection()->map(function (Product $product): array {
                $variants = $product->master?->variants?->values() ?? collect([$product]);
                $showcases = $variants->pluck('phoneType.name')->filter()->unique()->values();
                $cameraShapes = $variants->pluck('phoneType.camera_shape')->filter()->unique()->values();
                $antigoresSizes = $variants->pluck('phoneType.antigores_size')->filter()->unique()->values();
                $categoryImages = $variants
                    ->map(fn (Product $variant) => [
                        'name' => $variant->category->name ?? '-',
                        'url' => $variant->category?->image_path ? asset('storage/'.$variant->category->image_path) : null,
                    ])
                    ->unique(fn (array $item) => ($item['name'] ?? '').'|'.($item['url'] ?? ''))
                    ->values();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_note' => e(Str::limit((string) $product->product_note, 80)),
                    'is_visible_for_affiliator' => $product->is_visible_for_affiliator,
                    'category_images' => $categoryImages,
                    'brand' => $product->brand->name,
                    'brand_image' => $product->brand->image_path ? asset('storage/'.$product->brand->image_path) : null,
                    'showcase' => $showcases->implode(', '),
                    'antigores_size' => $antigoresSizes->implode(', '),
                    'camera_shape' => $cameraShapes->implode(', '),
                    'edit_url' => route('admin.products.edit', $product),
                    'delete_url' => route('admin.products.destroy', $product),
                    'visibility_url' => route('admin.products.visibility', $product),
                ];
            })->values(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $sourceProduct = null;
        $sourceVariants = collect();
        $sourceProductId = $request->integer('source_product_id');
        if ($sourceProductId > 0) {
            $sourceProduct = Product::query()
                ->with(['master.variants:id,product_master_id,category_id,phone_type_id'])
                ->find($sourceProductId);
            if ($sourceProduct?->master) {
                $sourceVariants = $sourceProduct->master->variants->map(fn (Product $variant) => [
                    'category_id' => $variant->category_id,
                    'phone_type_id' => $variant->phone_type_id,
                ])->values();
            } elseif ($sourceProduct) {
                $sourceVariants = collect([[
                    'category_id' => $sourceProduct->category_id,
                    'phone_type_id' => $sourceProduct->phone_type_id,
                ]]);
            }
        }

        return view('admin.products.create', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->whereNull('category_id')->orderBy('name')->get(['id', 'name']),
            'phoneTypes' => PhoneType::query()->orderBy('name')->get(['id', 'name']),
            'sourceProduct' => $sourceProduct,
            'sourceVariants' => $sourceVariants,
            'returnTo' => (string) $request->query('return_to', route('admin.products.index')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['required', Rule::exists('brands', 'id')->where(fn ($query) => $query->whereNull('category_id'))],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.category_id' => ['required', 'exists:categories,id'],
            'variants.*.phone_type_id' => ['required', 'exists:phone_types,id'],
            'product_note' => ['nullable', 'string', 'max:1000'],
            'is_visible_for_affiliator' => ['nullable', 'boolean'],
        ], [
            'brand_id.exists' => 'Brand harus berasal dari Master Brands.',
        ]);
        $isVisible = (bool) ($validated['is_visible_for_affiliator'] ?? true);
        $master = $this->resolveOrCreateMaster(
            $validated['name'],
            (int) $validated['brand_id'],
            $validated['product_note'] ?? null,
            $isVisible
        );

        $created = 0;
        foreach ($validated['variants'] as $variant) {
            $exists = Product::query()
                ->where('product_master_id', $master->id)
                ->where('category_id', $variant['category_id'])
                ->exists();

            if ($exists) {
                continue;
            }

            Product::query()->create([
                'product_master_id' => $master->id,
                'name' => $validated['name'],
                'category_id' => $variant['category_id'],
                'brand_id' => $validated['brand_id'],
                'phone_type_id' => $variant['phone_type_id'],
                'product_note' => $validated['product_note'] ?? null,
                'is_visible_for_affiliator' => $isVisible,
            ]);
            $created++;
        }

        if ($created === 0) {
            return redirect()->route('admin.products.index')->with('error', 'Semua varian produk yang diinput sudah ada.');
        }

        return redirect()->route('admin.products.index')->with('success', "Produk berhasil ditambahkan ({$created} varian).");
    }

    public function edit(Product $product): View
    {
        $master = $product->master;
        $variants = $master?->variants()->with(['category:id,name', 'phoneType:id,name'])->orderBy('category_id')->get()
            ?? collect([$product->load(['category:id,name', 'phoneType:id,name'])]);

        return view('admin.products.edit', [
            'product' => $product,
            'master' => $master,
            'variants' => $variants,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->whereNull('category_id')->orderBy('name')->get(['id', 'name']),
            'phoneTypes' => PhoneType::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['required', Rule::exists('brands', 'id')->where(fn ($query) => $query->whereNull('category_id'))],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.category_id' => ['required', 'exists:categories,id'],
            'variants.*.phone_type_id' => ['required', 'exists:phone_types,id'],
            'product_note' => ['nullable', 'string', 'max:1000'],
            'is_visible_for_affiliator' => ['nullable', 'boolean'],
        ], [
            'brand_id.exists' => 'Brand harus berasal dari Master Brands.',
        ]);
        $isVisible = (bool) ($validated['is_visible_for_affiliator'] ?? false);

        $master = $product->master ?? $this->resolveOrCreateMaster(
            $product->name,
            (int) $product->brand_id,
            $product->product_note,
            (bool) $product->is_visible_for_affiliator
        );
        $master->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'product_note' => $validated['product_note'] ?? null,
            'is_visible_for_affiliator' => $isVisible,
        ]);

        $variantRows = collect($validated['variants'])
            ->map(fn (array $row) => [
                'category_id' => (int) $row['category_id'],
                'phone_type_id' => (int) $row['phone_type_id'],
            ]);
        if ($variantRows->pluck('category_id')->count() !== $variantRows->pluck('category_id')->unique()->count()) {
            return back()
                ->withInput()
                ->withErrors(['variants' => 'Kategori pada varian tidak boleh duplikat.']);
        }

        $existingVariants = $master->variants()->get()->keyBy('category_id');
        $keptVariantIds = [];
        foreach ($variantRows as $variantRow) {
            $existing = $existingVariants->get($variantRow['category_id']);
            if ($existing) {
                $existing->update([
                    'phone_type_id' => $variantRow['phone_type_id'],
                ]);
                $keptVariantIds[] = $existing->id;
            } else {
                $created = Product::query()->create([
                    'product_master_id' => $master->id,
                    'name' => $master->name,
                    'category_id' => $variantRow['category_id'],
                    'brand_id' => $master->brand_id,
                    'phone_type_id' => $variantRow['phone_type_id'],
                    'product_note' => $master->product_note,
                    'is_visible_for_affiliator' => $master->is_visible_for_affiliator,
                ]);
                $keptVariantIds[] = $created->id;
            }
        }
        $master->variants()->whereNotIn('id', $keptVariantIds)->delete();
        $this->syncMasterToVariants($master);

        return $this->redirectToIndex($request)->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $master = $product->master;
        if ($master) {
            $deletedVariants = $master->variants()->count();
            $master->variants()->delete();
            $master->delete();

            return $this->redirectToIndex($request)->with('success', "Produk berhasil dihapus ({$deletedVariants} varian).");
        }

        $product->delete();

        return $this->redirectToIndex($request)->with('success', 'Produk berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $masterIds = Product::query()
            ->whereIn('id', $validated['product_ids'])
            ->whereNotNull('product_master_id')
            ->pluck('product_master_id')
            ->unique()
            ->values();
        $deleted = 0;
        if ($masterIds->isNotEmpty()) {
            $deleted = Product::query()->whereIn('product_master_id', $masterIds)->delete();
            ProductMaster::query()->whereIn('id', $masterIds)->delete();
        } else {
            $deleted = Product::query()->whereIn('id', $validated['product_ids'])->delete();
        }

        return $this->redirectToIndex($request)->with('success', "Berhasil menghapus {$deleted} varian produk.");
    }

    public function updateVisibility(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'is_visible_for_affiliator' => ['required', 'boolean'],
        ]);

        $isVisible = (bool) $validated['is_visible_for_affiliator'];
        $master = $product->master;
        if ($master) {
            $master->update(['is_visible_for_affiliator' => $isVisible]);
            $this->syncMasterToVariants($master);
        } else {
            $product->update(['is_visible_for_affiliator' => $isVisible]);
        }

        return $this->redirectToIndex($request)->with(
            'success',
            $isVisible
                ? 'Produk ditampilkan untuk affiliator.'
                : 'Produk disembunyikan dari affiliator.'
        );
    }

    public function bulkUpdateVisibility(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'is_visible_for_affiliator' => ['required', 'boolean'],
        ]);

        $isVisible = (bool) $validated['is_visible_for_affiliator'];
        $selectedProducts = Product::query()->whereIn('id', $validated['product_ids'])->get(['id', 'product_master_id']);
        $masterIds = $selectedProducts->pluck('product_master_id')->filter()->unique()->values();
        if ($masterIds->isNotEmpty()) {
            ProductMaster::query()->whereIn('id', $masterIds)->update(['is_visible_for_affiliator' => $isVisible]);
            Product::query()->whereIn('product_master_id', $masterIds)->update(['is_visible_for_affiliator' => $isVisible]);
        }
        $affected = $selectedProducts->count();

        return $this->redirectToIndex($request)->with(
            'success',
            $isVisible
                ? "Berhasil menampilkan {$affected} produk untuk affiliator."
                : "Berhasil menyembunyikan {$affected} produk dari affiliator."
        );
    }

    private function redirectToIndex(Request $request): RedirectResponse
    {
        return redirect()->to($this->redirectPath($request));
    }

    private function redirectPath(Request $request): string
    {
        $path = (string) $request->input('redirect_to', route('admin.products.index'));
        if (str_starts_with($path, '/')) {
            return $path;
        }
        $parsed = parse_url($path);
        if (is_array($parsed) && isset($parsed['path']) && str_starts_with((string) $parsed['path'], '/')) {
            return $parsed['path'].(isset($parsed['query']) ? '?'.$parsed['query'] : '');
        }

        return route('admin.products.index');
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
                    $master = $this->resolveOrCreateMaster(
                        $row['name'],
                        (int) $row['brand_id'],
                        $row['product_note'] ?? null,
                        false
                    );
                    $exists = Product::query()
                        ->where('product_master_id', $master->id)
                        ->where('category_id', $row['category_id'])
                        ->exists();
                    if ($exists) {
                        $skipped++;

                        return;
                    }

                    Product::query()->create([
                        'product_master_id' => $master->id,
                        'name' => $row['name'],
                        'category_id' => $row['category_id'],
                        'brand_id' => $row['brand_id'],
                        'phone_type_id' => $row['phone_type_id'],
                        'product_note' => $row['product_note'] ?? null,
                        'is_visible_for_affiliator' => false,
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
                $master = $this->resolveOrCreateMaster(
                    $row['name'],
                    (int) $row['brand_id'],
                    $row['product_note'] ?? null,
                    false
                );
                $exists = Product::query()
                    ->where('product_master_id', $master->id)
                    ->where('category_id', $row['category_id'])
                    ->exists();
                if ($exists) {
                    continue;
                }
                Product::query()->create([
                    'product_master_id' => $master->id,
                    'name' => $row['name'],
                    'category_id' => $row['category_id'],
                    'brand_id' => $row['brand_id'],
                    'phone_type_id' => $row['phone_type_id'],
                    'product_note' => $row['product_note'] ?? null,
                    'is_visible_for_affiliator' => false,
                ]);
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
        $categories = Category::query()->orderBy('name')->pluck('name')->values();
        $writer = new Writer;
        $writer->openToFile($path);
        $header = array_merge(['nama_produk', 'brand', 'catatan_produk'], $categories->all());
        $writer->addRow(Row::fromValues($header));

        $sampleRow = ['Antigores Samsung A15', 'Samsung', 'Contoh catatan produk'];
        foreach ($categories as $categoryName) {
            $normalized = Str::lower($categoryName);
            $sampleRow[] = str_contains($normalized, 'privacy')
                ? 'p-02'
                : (str_contains($normalized, 'bening') ? 'ai-01' : '');
        }
        $writer->addRow(Row::fromValues($sampleRow));
        $writer->close();

        return response()->download(
            $path,
            'template-import-produk.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function exportFiltered(Request $request): BinaryFileResponse
    {
        $products = $this->filteredQuery($request)->get();
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $filename = 'produk-filtered-'.now()->format('Ymd-His').'.xlsx';
        $path = storage_path("app/{$filename}");

        $writer = new Writer;
        $writer->openToFile($path);
        $header = array_merge(['nama_produk', 'brand', 'catatan_produk'], $categories->pluck('name')->all());
        $writer->addRow(Row::fromValues($header));

        $grouped = [];
        foreach ($products as $product) {
            $sourceVariants = $product->master?->variants?->values() ?? collect([$product]);
            $key = (string) ($product->product_master_id ?: $product->id);
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'name' => $product->master?->name ?? $product->name,
                    'brand' => $product->brand->name,
                    'product_note' => (string) ($product->master?->product_note ?? $product->product_note ?? ''),
                    'category_showcases' => [],
                ];
            }

            foreach ($sourceVariants as $variant) {
                $showcaseName = $variant->phoneType->name ?? null;
                if ($showcaseName === null) {
                    continue;
                }
                $grouped[$key]['category_showcases'][$variant->category_id][] = $showcaseName;
            }
        }

        foreach ($grouped as $rowData) {
            $row = [
                $rowData['name'],
                $rowData['brand'],
                $rowData['product_note'],
            ];

            foreach ($categories as $category) {
                $showcases = array_values(array_unique($rowData['category_showcases'][$category->id] ?? []));
                $row[] = implode(',', $showcases);
            }

            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();

        return response()->download(
            $path,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
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
        $headerCategoryMap = [];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $line++;
                    $cells = $row->toArray();
                    if ($isHeader) {
                        $headerValues = array_map(fn ($value) => trim((string) $value), $cells);
                        if (count($headerValues) < 4) {
                            throw new \RuntimeException('Format header import produk tidak valid.');
                        }
                        $headerCategoryMap = [];
                        foreach (array_slice($headerValues, 3) as $index => $categoryHeader) {
                            if ($categoryHeader === '') {
                                continue;
                            }
                            $category = Category::query()->whereRaw('LOWER(name) = ?', [Str::lower($categoryHeader)])->first();
                            if (! $category) {
                                throw new \RuntimeException("Kategori '{$categoryHeader}' pada header tidak ditemukan.");
                            }
                            $headerCategoryMap[3 + $index] = $category;
                        }
                        $isHeader = false;

                        continue;
                    }

                    $name = trim((string) ($cells[0] ?? ''));
                    $brandName = trim((string) ($cells[1] ?? ''));
                    $productNote = trim((string) ($cells[2] ?? ''));

                    $allVariantCells = '';
                    foreach (array_keys($headerCategoryMap) as $columnIndex) {
                        $allVariantCells .= trim((string) ($cells[$columnIndex] ?? ''));
                    }

                    if ($name === '' && $brandName === '' && $productNote === '' && $allVariantCells === '') {
                        continue;
                    }
                    if ($name === '' || $brandName === '') {
                        $previewRows[] = [
                            'line' => $line,
                            'name' => $name,
                            'category' => '-',
                            'brand' => $brandName,
                            'showcase' => '-',
                            'product_note' => $productNote,
                            'status' => 'error',
                            'message' => 'Kolom nama_produk dan brand wajib diisi.',
                        ];
                        $errorCount++;

                        continue;
                    }

                    $brand = Brand::query()
                        ->whereNull('category_id')
                        ->whereRaw('LOWER(name) = ?', [Str::lower($brandName)])
                        ->first();
                    if (! $brand) {
                        $previewRows[] = [
                            'line' => $line,
                            'name' => $name,
                            'category' => '-',
                            'brand' => $brandName,
                            'showcase' => '-',
                            'product_note' => $productNote,
                            'status' => 'error',
                            'message' => "Master brand '{$brandName}' tidak ditemukan.",
                        ];
                        $errorCount++;

                        continue;
                    }

                    $rowHasVariant = false;
                    foreach ($headerCategoryMap as $columnIndex => $category) {
                        $showcaseCell = trim((string) ($cells[$columnIndex] ?? ''));
                        if ($showcaseCell === '') {
                            continue;
                        }
                        $rowHasVariant = true;

                        if (str_contains($showcaseCell, ',')) {
                            $previewRows[] = [
                                'line' => $line,
                                'name' => $name,
                                'category' => $category->name,
                                'brand' => $brandName,
                                'showcase' => $showcaseCell,
                                'product_note' => $productNote,
                                'status' => 'error',
                                'message' => 'Satu kolom kategori hanya boleh berisi satu etalase.',
                            ];
                            $errorCount++;

                            continue;
                        }

                        $showcaseName = $showcaseCell;
                        $phoneType = PhoneType::query()->whereRaw('LOWER(name) = ?', [Str::lower($showcaseName)])->first();
                        if (! $phoneType) {
                            $previewRows[] = [
                                'line' => $line,
                                'name' => $name,
                                'category' => $category->name,
                                'brand' => $brandName,
                                'showcase' => $showcaseName,
                                'product_note' => $productNote,
                                'status' => 'error',
                                'message' => "Etalase '{$showcaseName}' tidak ditemukan.",
                            ];
                            $errorCount++;

                            continue;
                        }

                        $exists = Product::query()
                            ->where('name', $name)
                            ->where('category_id', $category->id)
                            ->where('brand_id', $brand->id)
                            ->exists();
                        if ($exists) {
                            $previewRows[] = [
                                'line' => $line,
                                'name' => $name,
                                'category' => $category->name,
                                'brand' => $brandName,
                                'showcase' => $showcaseName,
                                'product_note' => $productNote,
                                'status' => 'duplicate',
                                'message' => 'Duplikat: kombinasi produk, kategori, dan brand sudah ada.',
                            ];
                            $duplicateCount++;

                            continue;
                        }

                        $previewRows[] = [
                            'line' => $line,
                            'name' => $name,
                            'category' => $category->name,
                            'brand' => $brandName,
                            'showcase' => $showcaseName,
                            'product_note' => $productNote,
                            'status' => 'valid',
                            'message' => 'Siap diimport.',
                        ];
                        $toInsert[] = [
                            'name' => $name,
                            'category_id' => $category->id,
                            'brand_id' => $brand->id,
                            'phone_type_id' => $phoneType->id,
                            'product_note' => $productNote !== '' ? $productNote : null,
                            'is_visible_for_affiliator' => false,
                        ];
                        $validCount++;
                    }

                    if (! $rowHasVariant) {
                        $previewRows[] = [
                            'line' => $line,
                            'name' => $name,
                            'category' => '-',
                            'brand' => $brandName,
                            'showcase' => '-',
                            'product_note' => $productNote,
                            'status' => 'error',
                            'message' => 'Isi minimal satu kolom kategori dengan nama etalase.',
                        ];
                        $errorCount++;
                    }
                }
                break;
            }
        } catch (\RuntimeException $exception) {
            return [
                'to_insert' => [],
                'preview_rows' => [[
                    'line' => 1,
                    'name' => '-',
                    'category' => '-',
                    'brand' => '-',
                    'showcase' => '-',
                    'product_note' => '-',
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ]],
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 1,
            ];
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

        $baseQuery = Product::query()
            ->with(['category:id,name,image_path', 'brand:id,name,image_path', 'phoneType:id,name,antigores_size,camera_shape', 'master:id,name,brand_id,product_note,is_visible_for_affiliator'])
            ->when($keyword !== '', fn ($query) => $query->where(function ($searchQuery) use ($keyword) {
                $searchQuery
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('product_note', 'like', "%{$keyword}%")
                    ->orWhereHas('phoneType', fn ($phoneTypeQuery) => $phoneTypeQuery->where('antigores_size', 'like', "%{$keyword}%"));
            }))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($brandId, fn ($query) => $query->where('brand_id', $brandId))
            ->when($phoneTypeId, fn ($query) => $query->where('phone_type_id', $phoneTypeId));

        $representativeIds = (clone $baseQuery)
            ->selectRaw('MAX(id) as id')
            ->groupBy(DB::raw('COALESCE(product_master_id, id)'));

        return Product::query()
            ->with([
                'category:id,name,image_path',
                'brand:id,name,image_path',
                'phoneType:id,name,antigores_size,camera_shape',
                'master:id,name,brand_id,product_note,is_visible_for_affiliator',
                'master.variants:id,product_master_id,category_id,phone_type_id',
                'master.variants.category:id,name,image_path',
                'master.variants.phoneType:id,name,antigores_size,camera_shape',
            ])
            ->whereIn('id', $representativeIds)
            ->orderByDesc('id');
    }

    private function resolveOrCreateMaster(string $name, int $brandId, ?string $productNote, bool $isVisible): ProductMaster
    {
        $normalizedName = trim($name);
        $normalizedNote = trim((string) $productNote);

        $master = ProductMaster::query()
            ->where('brand_id', $brandId)
            ->whereRaw('LOWER(name) = ?', [Str::lower($normalizedName)])
            ->whereRaw('LOWER(COALESCE(product_note, "")) = ?', [Str::lower($normalizedNote)])
            ->first();

        if (! $master) {
            return ProductMaster::query()->create([
                'name' => $normalizedName,
                'brand_id' => $brandId,
                'product_note' => $normalizedNote !== '' ? $normalizedNote : null,
                'is_visible_for_affiliator' => $isVisible,
            ]);
        }

        if ($isVisible && ! $master->is_visible_for_affiliator) {
            $master->update(['is_visible_for_affiliator' => true]);
        }

        return $master;
    }

    private function syncMasterToVariants(ProductMaster $master): void
    {
        Product::query()
            ->where('product_master_id', $master->id)
            ->update([
                'name' => $master->name,
                'brand_id' => $master->brand_id,
                'product_note' => $master->product_note,
                'is_visible_for_affiliator' => $master->is_visible_for_affiliator,
            ]);
    }
}
