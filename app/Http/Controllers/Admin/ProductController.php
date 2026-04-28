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
            'precisionStatuses' => ProductMaster::precisionStatusOptions(),
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
                    'precision_status' => $this->resolvePrecisionStatus($product),
                    'precision_status_label' => ProductMaster::precisionStatusLabel($this->resolvePrecisionStatus($product)),
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
            'precisionStatuses' => ProductMaster::precisionStatusOptions(),
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
            'precision_status' => ['required', Rule::in(array_keys(ProductMaster::precisionStatusOptions()))],
        ], [
            'brand_id.exists' => 'Brand harus berasal dari Master Brands.',
        ]);
        $isVisible = (bool) ($validated['is_visible_for_affiliator'] ?? true);
        $precisionStatus = ProductMaster::normalizePrecisionStatus($validated['precision_status']);
        $master = $this->resolveOrCreateMaster(
            $validated['name'],
            (int) $validated['brand_id'],
            $validated['product_note'] ?? null,
            $isVisible,
            $precisionStatus
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
                'precision_status' => $precisionStatus,
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
            'precisionStatuses' => ProductMaster::precisionStatusOptions(),
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
            'precision_status' => ['required', Rule::in(array_keys(ProductMaster::precisionStatusOptions()))],
        ], [
            'brand_id.exists' => 'Brand harus berasal dari Master Brands.',
        ]);
        $isVisible = (bool) ($validated['is_visible_for_affiliator'] ?? false);
        $precisionStatus = ProductMaster::normalizePrecisionStatus($validated['precision_status']);

        $master = $product->master ?? $this->resolveOrCreateMaster(
            $product->name,
            (int) $product->brand_id,
            $product->product_note,
            (bool) $product->is_visible_for_affiliator,
            $this->resolvePrecisionStatus($product)
        );
        $master->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'product_note' => $validated['product_note'] ?? null,
            'is_visible_for_affiliator' => $isVisible,
            'precision_status' => $precisionStatus,
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
                    'precision_status' => $master->precision_status,
                ]);
                $keptVariantIds[] = $created->id;
            }
        }
        $master->variants()->whereNotIn('id', $keptVariantIds)->delete();
        $this->syncMasterToVariants($master);
        $this->syncLcdGroupMembersFromMaster($master);

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
                        false,
                        ProductMaster::normalizePrecisionStatus($row['precision_status'] ?? null)
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
                        'precision_status' => ProductMaster::normalizePrecisionStatus($row['precision_status'] ?? null),
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
                    false,
                    ProductMaster::normalizePrecisionStatus($row['precision_status'] ?? null)
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
                    'precision_status' => ProductMaster::normalizePrecisionStatus($row['precision_status'] ?? null),
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
        $header = array_merge(['nama_produk', 'brand', 'catatan_produk', 'status_presisi'], $categories->all());
        $writer->addRow(Row::fromValues($header));

        $sampleRow = ['Antigores Samsung A15', 'Samsung', 'Contoh catatan produk', 'Belum di tes'];
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
        $header = array_merge(['nama_produk', 'brand', 'catatan_produk', 'status_presisi'], $categories->pluck('name')->all());
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
                    'precision_status' => $this->resolvePrecisionStatus($product),
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
                ProductMaster::precisionStatusLabel($rowData['precision_status']),
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
        $hasPrecisionStatusColumn = false;
        $categoryStartIndex = 3;

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
                        $statusHeader = Str::lower($headerValues[3] ?? '');
                        $hasPrecisionStatusColumn = in_array($statusHeader, [
                            'status_presisi',
                            'status presisi',
                            'status',
                        ], true);
                        $categoryStartIndex = $hasPrecisionStatusColumn ? 4 : 3;
                        $headerCategoryMap = [];
                        foreach (array_slice($headerValues, $categoryStartIndex) as $index => $categoryHeader) {
                            if ($categoryHeader === '') {
                                continue;
                            }
                            $category = Category::query()->whereRaw('LOWER(name) = ?', [Str::lower($categoryHeader)])->first();
                            if (! $category) {
                                throw new \RuntimeException("Kategori '{$categoryHeader}' pada header tidak ditemukan.");
                            }
                            $headerCategoryMap[$categoryStartIndex + $index] = $category;
                        }
                        $isHeader = false;

                        continue;
                    }

                    $name = trim((string) ($cells[0] ?? ''));
                    $brandName = trim((string) ($cells[1] ?? ''));
                    $productNote = trim((string) ($cells[2] ?? ''));
                    $precisionStatus = ProductMaster::normalizePrecisionStatus(
                        $hasPrecisionStatusColumn
                            ? (string) ($cells[3] ?? '')
                            : ProductMaster::PRECISION_STATUS_BELUM_DITES
                    );

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
                            'precision_status' => ProductMaster::precisionStatusLabel($precisionStatus),
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
                            'precision_status' => ProductMaster::precisionStatusLabel($precisionStatus),
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
                                'precision_status' => ProductMaster::precisionStatusLabel($precisionStatus),
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
                                'precision_status' => ProductMaster::precisionStatusLabel($precisionStatus),
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
                                'precision_status' => ProductMaster::precisionStatusLabel($precisionStatus),
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
                            'precision_status' => ProductMaster::precisionStatusLabel($precisionStatus),
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
                            'precision_status' => $precisionStatus,
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
                            'precision_status' => ProductMaster::precisionStatusLabel($precisionStatus),
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
                    'precision_status' => '-',
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
        $categoryIds = collect($request->input('category_ids', []))
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->values();
        if ($categoryIds->isEmpty() && $request->filled('category_id')) {
            $legacyCategoryId = $request->integer('category_id');
            if ($legacyCategoryId > 0) {
                $categoryIds = collect([$legacyCategoryId]);
            }
        }
        $brandId = $request->integer('brand_id');
        $phoneTypeId = $request->integer('phone_type_id');
        $precisionStatus = ProductMaster::normalizePrecisionStatus((string) $request->string('precision_status'));
        $hasPrecisionStatusFilter = $request->filled('precision_status');
        $selectedCategoryCount = $categoryIds->count();

        $baseQuery = Product::query()
            ->with(['category:id,name,image_path', 'brand:id,name,image_path', 'phoneType:id,name,antigores_size,camera_shape', 'master:id,name,brand_id,product_note,is_visible_for_affiliator,precision_status'])
            ->when($keyword !== '', fn ($query) => $query->where(function ($searchQuery) use ($keyword) {
                $searchQuery
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('product_note', 'like', "%{$keyword}%")
                    ->orWhereHas('phoneType', fn ($phoneTypeQuery) => $phoneTypeQuery->where('antigores_size', 'like', "%{$keyword}%"));
            }))
            ->when($brandId, fn ($query) => $query->where('brand_id', $brandId))
            ->when($phoneTypeId, fn ($query) => $query->where('phone_type_id', $phoneTypeId))
            ->when($hasPrecisionStatusFilter, fn ($query) => $query->where('precision_status', $precisionStatus));

        if ($selectedCategoryCount > 0) {
            $masterKeyExpression = 'COALESCE(products.product_master_id, products.id)';
            $selectedCategoryValues = $categoryIds->all();

            $baseQuery->whereNotExists(function ($subQuery) use ($masterKeyExpression, $selectedCategoryValues) {
                $subQuery->selectRaw('1')
                    ->from('products as category_check_outside')
                    ->whereRaw("COALESCE(category_check_outside.product_master_id, category_check_outside.id) = {$masterKeyExpression}")
                    ->whereNotIn('category_check_outside.category_id', $selectedCategoryValues);
            });

            foreach ($selectedCategoryValues as $selectedCategoryId) {
                $baseQuery->whereExists(function ($subQuery) use ($masterKeyExpression, $selectedCategoryId) {
                    $subQuery->selectRaw('1')
                        ->from('products as category_check_inside')
                        ->whereRaw("COALESCE(category_check_inside.product_master_id, category_check_inside.id) = {$masterKeyExpression}")
                        ->where('category_check_inside.category_id', $selectedCategoryId);
                });
            }
        }

        $representativeIds = (clone $baseQuery)
            ->selectRaw('MAX(id) as id')
            ->groupBy(DB::raw('COALESCE(product_master_id, id)'));

        return Product::query()
            ->with([
                'category:id,name,image_path',
                'brand:id,name,image_path',
                'phoneType:id,name,antigores_size,camera_shape',
                'master:id,name,brand_id,product_note,is_visible_for_affiliator,precision_status',
                'master.variants:id,product_master_id,category_id,phone_type_id,precision_status',
                'master.variants.category:id,name,image_path',
                'master.variants.phoneType:id,name,antigores_size,camera_shape',
            ])
            ->whereIn('id', $representativeIds)
            ->orderByDesc('id');
    }

    private function resolveOrCreateMaster(string $name, int $brandId, ?string $productNote, bool $isVisible, string $precisionStatus): ProductMaster
    {
        $normalizedName = trim($name);
        $normalizedNote = trim((string) $productNote);
        $normalizedStatus = ProductMaster::normalizePrecisionStatus($precisionStatus);

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
                'precision_status' => $normalizedStatus,
            ]);
        }

        $updates = [];
        if ($isVisible && ! $master->is_visible_for_affiliator) {
            $updates['is_visible_for_affiliator'] = true;
        }
        if ($master->precision_status !== $normalizedStatus) {
            $updates['precision_status'] = $normalizedStatus;
        }
        if (! empty($updates)) {
            $master->update($updates);
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
                'precision_status' => ProductMaster::normalizePrecisionStatus($master->precision_status),
            ]);
    }

    private function syncLcdGroupMembersFromMaster(ProductMaster $sourceMaster): void
    {
        $sourceGroupIds = DB::table('lcd_group_product_master')
            ->where('product_master_id', $sourceMaster->id)
            ->pluck('lcd_group_id')
            ->unique()
            ->values();
        if ($sourceGroupIds->isEmpty()) {
            return;
        }

        $targetMasterIds = DB::table('lcd_group_product_master')
            ->whereIn('lcd_group_id', $sourceGroupIds->all())
            ->where('product_master_id', '!=', $sourceMaster->id)
            ->pluck('product_master_id')
            ->unique()
            ->values();
        if ($targetMasterIds->isEmpty()) {
            return;
        }

        $sourceVariants = $sourceMaster->variants()
            ->get(['category_id', 'phone_type_id'])
            ->keyBy('category_id');

        ProductMaster::query()
            ->whereIn('id', $targetMasterIds->all())
            ->with('variants')
            ->get()
            ->each(function (ProductMaster $targetMaster) use ($sourceMaster, $sourceVariants): void {
                $targetMaster->update([
                    'product_note' => $sourceMaster->product_note,
                    'precision_status' => ProductMaster::normalizePrecisionStatus($sourceMaster->precision_status),
                ]);

                $existingVariants = $targetMaster->variants->keyBy('category_id');
                $keptVariantIds = [];
                foreach ($sourceVariants as $sourceVariant) {
                    $categoryId = (int) $sourceVariant->category_id;
                    $phoneTypeId = (int) $sourceVariant->phone_type_id;
                    $existingVariant = $existingVariants->get($categoryId);
                    if ($existingVariant) {
                        $existingVariant->update(['phone_type_id' => $phoneTypeId]);
                        $keptVariantIds[] = $existingVariant->id;

                        continue;
                    }

                    $created = Product::query()->create([
                        'product_master_id' => $targetMaster->id,
                        'name' => $targetMaster->name,
                        'category_id' => $categoryId,
                        'brand_id' => $targetMaster->brand_id,
                        'phone_type_id' => $phoneTypeId,
                        'product_note' => $targetMaster->product_note,
                        'is_visible_for_affiliator' => $targetMaster->is_visible_for_affiliator,
                        'precision_status' => ProductMaster::normalizePrecisionStatus($targetMaster->precision_status),
                    ]);
                    $keptVariantIds[] = $created->id;
                }

                $targetMaster->variants()->whereNotIn('id', $keptVariantIds)->delete();
                $this->syncMasterToVariants($targetMaster);
            });
    }

    private function resolvePrecisionStatus(Product $product): string
    {
        if ($product->master?->precision_status) {
            return ProductMaster::normalizePrecisionStatus($product->master->precision_status);
        }

        return ProductMaster::normalizePrecisionStatus($product->precision_status);
    }
}
