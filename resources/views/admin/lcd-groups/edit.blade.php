@extends('layouts.app')

@section('content')
    @php($returnTo = request('return_to', route('admin.lcd-groups.index')))
    @php($selectedOptionIds = collect(old('product_master_ids', $selectedOptionIds ?? []))->map(fn ($value) => (string) $value)->all())
    <h1 class="h5 mb-3">Edit Grup LCD</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.lcd-groups.update', $lcdGroup) }}" class="vstack gap-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ $returnTo }}">
                <div>
                    <label class="form-label">Nama Grup LCD</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $lcdGroup->name) }}" required>
                </div>
                <div>
                    <label class="form-label">Catatan</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Opsional">{{ old('note', $lcdGroup->note) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Pilih Produk Dalam Grup</label>
                    @php($selectedRows = collect($selectedOptionIds)->values())
                    @php($selectedRows = $selectedRows->isEmpty() ? collect(['']) : $selectedRows)
                    <div id="product-rows" class="vstack gap-2">
                        @foreach($selectedRows as $selectedId)
                            <div class="d-flex gap-2 product-row">
                                <select name="product_master_ids[]" class="form-select js-product-select" required>
                                    <option value="" disabled @selected((string) $selectedId === '')>Pilih produk</option>
                                    @foreach($productMasterOptions as $masterOption)
                                        <option
                                            value="{{ $masterOption['id'] }}"
                                            @selected((string) $masterOption['id'] === (string) $selectedId)
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
                    Produk dari grup lain ditampilkan sebagai nonaktif (disabled) beserta nama grupnya.
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
            const rowContainer = document.getElementById('product-rows');
            const addButton = document.getElementById('add-product-row');

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

            const bindRowEvents = (row) => {
                const select = row.querySelector('.js-product-select');
                const removeBtn = row.querySelector('.js-remove-row');

                select.addEventListener('change', () => {
                    if (hasDuplicateValue(select)) {
                        alert('Produk sudah dipilih pada baris lain.');
                        select.value = '';
                    }
                });

                removeBtn.addEventListener('click', () => {
                    row.remove();
                    updateRemoveButtons();
                });
            };

            addButton.addEventListener('click', () => {
                const firstRow = rowContainer.querySelector('.product-row');
                const newRow = firstRow.cloneNode(true);
                const newSelect = newRow.querySelector('.js-product-select');
                newSelect.value = '';
                rowContainer.appendChild(newRow);
                bindRowEvents(newRow);
                updateRemoveButtons();
            });

            rowContainer.querySelectorAll('.product-row').forEach(bindRowEvents);
            updateRemoveButtons();
        })();
    </script>
@endsection
