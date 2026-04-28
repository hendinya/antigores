@extends('layouts.app')

@section('content')
    @php
        $returnTo = request('return_to', route('admin.products.index'));
        $oldVariants = old('variants');
        if (!is_array($oldVariants) || count($oldVariants) === 0) {
            $baseVariants = isset($variants) ? $variants : collect([$product]);
            $oldVariants = collect($baseVariants)->map(function ($variant) {
                return [
                    'category_id' => $variant->category_id ?? null,
                    'phone_type_id' => $variant->phone_type_id ?? null,
                ];
            })->values()->all();
        }
    @endphp
    <h1 class="h5 mb-3">Edit Produk</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(($lcdGroupSyncImpact['affected_count'] ?? 0) > 0)
                <div class="alert alert-warning">
                    <div class="fw-semibold mb-1">Perubahan akan tersinkron ke produk lain di Grup LCD</div>
                    <div class="small mb-1">
                        Grup terkait:
                        {{ implode(', ', $lcdGroupSyncImpact['group_names'] ?? []) }}
                    </div>
                    <div class="small mb-1">
                        Data yang ikut berubah saat klik Update:
                        {{ implode('; ', $lcdGroupSyncImpact['sync_fields'] ?? []) }}.
                    </div>
                    <div class="small">
                        Produk terdampak ({{ $lcdGroupSyncImpact['affected_count'] }}):
                        {{ implode(', ', $lcdGroupSyncImpact['affected_products'] ?? []) }}
                    </div>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.products.update', $product) }}" class="vstack gap-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ $returnTo }}">
                <div>
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $master?->name ?? $product->name) }}" required>
                </div>
                <div>
                    <label class="form-label">Brand</label>
                    <select id="brand_id" name="brand_id" class="form-select" required>
                        <option value="">Pilih brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id', $master?->brand_id ?? $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Status Presisi</label>
                    <select name="precision_status" class="form-select" required>
                        @foreach($precisionStatuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected(old('precision_status', $master?->precision_status ?? $product->precision_status ?? \App\Models\ProductMaster::PRECISION_STATUS_BELUM_DITES) === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Varian Kategori + Etalase</label>
                        <button type="button" class="btn btn-outline-dark btn-sm" id="addVariantRowBtn">Tambah Varian</button>
                    </div>
                    <div id="variantsWrap" class="vstack gap-2">
                        @foreach(($oldVariants ?? []) as $index => $variant)
                            <div class="row g-2 align-items-end variant-row">
                                <div class="col-md-5">
                                    <label class="form-label">Kategori</label>
                                    <select name="variants[{{ $index }}][category_id]" class="form-select" required data-role="category">
                                        <option value="">Pilih kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string)($variant['category_id'] ?? '') === (string)$category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Etalase</label>
                                    <select name="variants[{{ $index }}][phone_type_id]" class="form-select" required data-role="phone_type">
                                        <option value="">Pilih etalase</option>
                                        @foreach($phoneTypes as $phoneType)
                                            <option value="{{ $phoneType->id }}" @selected((string)($variant['phone_type_id'] ?? '') === (string)$phoneType->id)>{{ $phoneType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-variant-btn">Hapus</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="form-label">Catatan Produk</label>
                    <textarea name="product_note" class="form-control" rows="3" placeholder="Isi catatan produk">{{ old('product_note', $master?->product_note ?? $product->product_note) }}</textarea>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="uiverse-switch">
                        <input type="hidden" name="is_visible_for_affiliator" value="0">
                        <input class="visibility-switch" type="checkbox" role="switch" id="is_visible_for_affiliator" name="is_visible_for_affiliator" value="1" @checked(old('is_visible_for_affiliator', $master?->is_visible_for_affiliator ?? $product->is_visible_for_affiliator))>
                        <span class="uiverse-slider"></span>
                    </label>
                    <label class="form-label mb-0" for="is_visible_for_affiliator">Tampilkan di halaman affiliator (/products)</label>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Update</button>
                    <a href="{{ $returnTo }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        (() => {
            const wrap = document.getElementById('variantsWrap');
            const addBtn = document.getElementById('addVariantRowBtn');
            if (!(wrap instanceof HTMLElement) || !(addBtn instanceof HTMLButtonElement)) {
                return;
            }

            const categoryOptions = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values());
            const phoneTypeOptions = @json($phoneTypes->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values());

            const buildOptions = (items, placeholder) => {
                const options = [`<option value="">${placeholder}</option>`];
                items.forEach((item) => options.push(`<option value="${item.id}">${item.name}</option>`));
                return options.join('');
            };

            const reindexRows = () => {
                wrap.querySelectorAll('.variant-row').forEach((row, index) => {
                    const categorySelect = row.querySelector('select[data-role="category"]');
                    const phoneTypeSelect = row.querySelector('select[data-role="phone_type"]');
                    if (categorySelect instanceof HTMLSelectElement) {
                        categorySelect.name = `variants[${index}][category_id]`;
                    }
                    if (phoneTypeSelect instanceof HTMLSelectElement) {
                        phoneTypeSelect.name = `variants[${index}][phone_type_id]`;
                    }
                });
            };

            addBtn.addEventListener('click', () => {
                const row = document.createElement('div');
                row.className = 'row g-2 align-items-end variant-row';
                row.innerHTML = `
                    <div class="col-md-5">
                        <label class="form-label">Kategori</label>
                        <select data-role="category" class="form-select" required>${buildOptions(categoryOptions, 'Pilih kategori')}</select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Etalase</label>
                        <select data-role="phone_type" class="form-select" required>${buildOptions(phoneTypeOptions, 'Pilih etalase')}</select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-variant-btn">Hapus</button>
                    </div>
                `;
                wrap.appendChild(row);
                reindexRows();
            });

            wrap.addEventListener('click', (event) => {
                const button = event.target.closest('.remove-variant-btn');
                if (!(button instanceof HTMLButtonElement)) {
                    return;
                }
                const rows = wrap.querySelectorAll('.variant-row');
                if (rows.length <= 1) {
                    return;
                }
                button.closest('.variant-row')?.remove();
                reindexRows();
            });
        })();
    </script>
@endsection
