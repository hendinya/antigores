@extends('layouts.app')

@section('content')
    @php
        $sourceProduct = $sourceProduct ?? null;
        $sourceVariants = collect($sourceVariants ?? []);
        $returnTo = $returnTo ?? route('admin.products.index');
        $defaultName = $sourceProduct?->name ? $sourceProduct->name.' Copy' : '';
    @endphp
    <h1 class="h5 mb-3">Tambah Produk</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.store') }}" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $defaultName) }}" required>
                </div>
                <div>
                    <label class="form-label">Brand</label>
                    <select id="brand_id" name="brand_id" class="form-select" required>
                        <option value="">Pilih brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id', $sourceProduct?->brand_id) == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Varian Kategori + Etalase</label>
                        <button type="button" class="btn btn-outline-dark btn-sm" id="addVariantRowBtn">Tambah Varian</button>
                    </div>
                    <div id="variantsWrap" class="vstack gap-2">
                        @php
                            $oldVariants = old('variants');
                            if (!is_array($oldVariants) || count($oldVariants) === 0) {
                                $oldVariants = $sourceVariants->isNotEmpty()
                                    ? $sourceVariants->toArray()
                                    : [[
                                        'category_id' => $sourceProduct?->category_id,
                                        'phone_type_id' => $sourceProduct?->phone_type_id,
                                    ]];
                            }
                        @endphp
                        @foreach($oldVariants as $index => $variant)
                            <div class="row g-2 align-items-end variant-row">
                                <div class="col-md-5">
                                    <label class="form-label">Kategori</label>
                                    <select name="variants[{{ $index }}][category_id]" class="form-select" required>
                                        <option value="">Pilih kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string)($variant['category_id'] ?? '') === (string)$category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Etalase</label>
                                    <select name="variants[{{ $index }}][phone_type_id]" class="form-select" required>
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
                    <textarea name="product_note" class="form-control" rows="3" placeholder="Isi catatan produk">{{ old('product_note', $sourceProduct?->product_note) }}</textarea>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="uiverse-switch">
                        <input type="hidden" name="is_visible_for_affiliator" value="0">
                        <input class="visibility-switch" type="checkbox" role="switch" id="is_visible_for_affiliator" name="is_visible_for_affiliator" value="1" @checked(old('is_visible_for_affiliator', $sourceProduct?->is_visible_for_affiliator ?? 1))>
                        <span class="uiverse-slider"></span>
                    </label>
                    <label class="form-label mb-0" for="is_visible_for_affiliator">Tampilkan di halaman affiliator (/products)</label>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Simpan</button>
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

            const appendRow = () => {
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
            };

            wrap.querySelectorAll('.variant-row').forEach((row) => {
                const categorySelect = row.querySelector('select[name*="[category_id]"]');
                const phoneTypeSelect = row.querySelector('select[name*="[phone_type_id]"]');
                categorySelect?.setAttribute('data-role', 'category');
                phoneTypeSelect?.setAttribute('data-role', 'phone_type');
            });
            reindexRows();

            addBtn.addEventListener('click', appendRow);
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
