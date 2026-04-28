@extends('layouts.app')

@section('content')
    <h1 class="h5 mb-3">Tambah Grup LCD</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.lcd-groups.store') }}" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label">Nama Grup LCD</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label class="form-label">Catatan</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Opsional">{{ old('note') }}</textarea>
                </div>
                <div>
                    <label class="form-label">Pilih Produk Dalam Grup</label>
                    @php($selectedRows = collect(old('product_master_ids', []))->map(fn ($value) => (string) $value)->values())
                    @php($selectedRows = $selectedRows->isEmpty() ? collect(['']) : $selectedRows)
                    <div id="product-rows" class="vstack gap-2">
                        @foreach($selectedRows as $selectedId)
                            <div class="d-flex gap-2 product-row">
                                <input
                                    type="text"
                                    class="form-control js-product-filter"
                                    placeholder="Cari produk..."
                                    autocomplete="off"
                                >
                                <select name="product_master_ids[]" class="form-select js-product-select" required>
                                    <option value="" disabled @selected($selectedId === '')>Pilih produk</option>
                                    @foreach($productMasterOptions as $masterOption)
                                        <option
                                            value="{{ $masterOption['id'] }}"
                                            @selected((string) $masterOption['id'] === $selectedId)
                                            @disabled(($masterOption['disabled'] ?? false) === true)
                                        >
                                            {{ $masterOption['name'] }}@if(!empty($masterOption['assigned_group_names'])) (dipakai di grup: {{ implode(', ', $masterOption['assigned_group_names']) }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-danger js-remove-row">Hapus</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-product-row" class="btn btn-outline-primary btn-sm mt-2">+ Tambah Produk</button>
                </div>
                <div class="small text-secondary">
                    Produk yang sudah masuk grup lain ditampilkan sebagai nonaktif (disabled) beserta nama grupnya.
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Simpan</button>
                    <a href="{{ route('admin.lcd-groups.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const rowContainer = document.getElementById('product-rows');
            const addButton = document.getElementById('add-product-row');
            const firstSelect = rowContainer.querySelector('.js-product-select');
            const selectOptionsHtml = firstSelect ? firstSelect.innerHTML : '';

            const updateRemoveButtons = () => {
                const rows = rowContainer.querySelectorAll('.product-row');
                rows.forEach((row) => {
                    const removeBtn = row.querySelector('.js-remove-row');
                    removeBtn.disabled = rows.length <= 1;
                });
            };

            const hasDuplicateValue = (currentSelect) => {
                const value = currentSelect.value;
                if (!value) {
                    return false;
                }
                const selects = Array.from(rowContainer.querySelectorAll('.js-product-select'));
                return selects.some((select) => select !== currentSelect && select.value === value);
            };

            const buildRow = () => {
                const row = document.createElement('div');
                row.className = 'd-flex gap-2 product-row';
                row.innerHTML = `
                    <input
                        type="text"
                        class="form-control js-product-filter"
                        placeholder="Cari produk..."
                        autocomplete="off"
                    >
                    <select name="product_master_ids[]" class="form-select js-product-select" required>
                        ${selectOptionsHtml}
                    </select>
                    <button type="button" class="btn btn-outline-danger js-remove-row">Hapus</button>
                `;
                row.querySelector('.js-product-select').value = '';
                return row;
            };

            addButton.addEventListener('click', () => {
                const newRow = buildRow();
                rowContainer.appendChild(newRow);
                updateRemoveButtons();
            });

            const filterOptions = (select, keyword) => {
                const normalized = keyword.trim().toLowerCase();
                Array.from(select.options).forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }
                    const matched = normalized === '' || option.text.toLowerCase().includes(normalized);
                    option.hidden = !matched;
                    if (option.selected) {
                        option.hidden = false;
                    }
                });
            };

            rowContainer.addEventListener('change', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLSelectElement) || !target.classList.contains('js-product-select')) {
                    return;
                }
                if (hasDuplicateValue(target)) {
                    alert('Produk sudah dipilih pada baris lain.');
                    target.value = '';
                }
            });

            rowContainer.addEventListener('input', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLInputElement) || !target.classList.contains('js-product-filter')) {
                    return;
                }
                const row = target.closest('.product-row');
                if (!row) {
                    return;
                }
                const select = row.querySelector('.js-product-select');
                if (!(select instanceof HTMLSelectElement)) {
                    return;
                }
                filterOptions(select, target.value);
            });

            rowContainer.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLElement) || !target.classList.contains('js-remove-row')) {
                    return;
                }
                const row = target.closest('.product-row');
                if (row) {
                    row.remove();
                    updateRemoveButtons();
                }
            });

            updateRemoveButtons();
        })();
    </script>
@endsection
