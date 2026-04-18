<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhoneType;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class PhoneTypeController extends Controller
{
    public function index(Request $request): View
    {
        $phoneTypes = $this->filteredQuery($request)
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.phone-types.index', [
            'phoneTypes' => $phoneTypes,
            'keyword' => trim((string) $request->string('keyword')),
        ]);
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

        return $this->redirectToIndex($request)->with('success', 'Etalase berhasil diperbarui.');
    }

    public function destroy(Request $request, PhoneType $phoneType): RedirectResponse
    {
        $productCount = $phoneType->products()->count();
        try {
            DB::transaction(function () use ($phoneType): void {
                $phoneType->products()->delete();
                $phoneType->delete();
            });
        } catch (QueryException) {
            return redirect()
                ->to($this->redirectPath($request))
                ->with('error', 'Etalase tidak dapat dihapus karena masih terhubung dengan data lain.');
        }

        return $this->redirectToIndex($request)->with(
            'success',
            $productCount > 0
                ? "Etalase berhasil dihapus beserta {$productCount} produk terkait."
                : 'Etalase berhasil dihapus.'
        );
    }

    public function template(): BinaryFileResponse
    {
        $path = storage_path('app/template-import-etalase.xlsx');
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['nama_etalase', 'bentuk_kamera', 'ukuran_antigores', 'masteran', 'link_belanja']));
        $writer->addRow(Row::fromValues(['AI-99', 'Tengah', '160 x 75', 'Contoh masteran etalase', 'https://example.com/produk-1']));
        $writer->close();

        return response()->download(
            $path,
            'template-import-etalase.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function exportFiltered(Request $request): BinaryFileResponse
    {
        $phoneTypes = $this->filteredQuery($request)->orderBy('name')->get();
        $filename = 'etalase-filtered-'.now()->format('Ymd-His').'.xlsx';
        $path = storage_path("app/{$filename}");

        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['nama_etalase', 'bentuk_kamera', 'ukuran_antigores', 'masteran', 'link_belanja']));

        foreach ($phoneTypes as $phoneType) {
            $writer->addRow(Row::fromValues([
                $phoneType->name,
                (string) ($phoneType->camera_shape ?? ''),
                (string) ($phoneType->antigores_size ?? ''),
                (string) ($phoneType->masteran ?? ''),
                (string) ($phoneType->shopping_link ?? ''),
            ]));
        }
        $writer->close();

        return response()->download(
            $path,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        $reader = new XlsxReader;
        $reader->open($validated['file']->getRealPath());

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $line = 1;

        DB::beginTransaction();
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $isHeader = true;
                foreach ($sheet->getRowIterator() as $row) {
                    $line++;
                    if ($isHeader) {
                        $isHeader = false;

                        continue;
                    }

                    $cells = $row->toArray();
                    $name = trim((string) ($cells[0] ?? ''));
                    $cameraShape = trim((string) ($cells[1] ?? ''));
                    $antigoresSize = trim((string) ($cells[2] ?? ''));
                    $masteran = trim((string) ($cells[3] ?? ''));
                    $shoppingLink = trim((string) ($cells[4] ?? ''));

                    if ($name === '' && $cameraShape === '' && $antigoresSize === '' && $masteran === '' && $shoppingLink === '') {
                        continue;
                    }
                    if ($name === '' || $cameraShape === '' || $antigoresSize === '') {
                        $skipped++;

                        continue;
                    }

                    $payload = [
                        'camera_shape' => $cameraShape,
                        'antigores_size' => $antigoresSize,
                        'masteran' => $masteran !== '' ? $masteran : null,
                        'shopping_link' => $shoppingLink !== '' ? $shoppingLink : null,
                    ];

                    $existing = PhoneType::query()->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                    if ($existing) {
                        $existing->update(array_merge(['name' => $name], $payload));
                        $updated++;
                    } else {
                        PhoneType::query()->create(array_merge(['name' => $name], $payload));
                        $created++;
                    }
                }
                break;
            }
            DB::commit();
        } catch (\Throwable) {
            DB::rollBack();

            return redirect()->route('admin.phone-types.index')->with('error', 'Import etalase gagal diproses.');
        } finally {
            $reader->close();
        }

        return redirect()->route('admin.phone-types.index')->with(
            'success',
            "Import etalase selesai. Baru: {$created}, diperbarui: {$updated}, dilewati: {$skipped}."
        );
    }

    private function redirectToIndex(Request $request): RedirectResponse
    {
        return redirect()->to($this->redirectPath($request));
    }

    private function redirectPath(Request $request): string
    {
        $path = (string) $request->input('redirect_to', route('admin.phone-types.index'));
        if (str_starts_with($path, '/')) {
            return $path;
        }
        $parsed = parse_url($path);
        if (is_array($parsed) && isset($parsed['path']) && str_starts_with((string) $parsed['path'], '/')) {
            return $parsed['path'].(isset($parsed['query']) ? '?'.$parsed['query'] : '');
        }

        return route('admin.phone-types.index');
    }

    private function filteredQuery(Request $request): Builder
    {
        $keyword = trim((string) $request->string('keyword'));

        return PhoneType::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('antigores_size', 'like', "%{$keyword}%")
                        ->orWhere('camera_shape', 'like', "%{$keyword}%")
                        ->orWhere('masteran', 'like', "%{$keyword}%");
                });
            });
    }
}
