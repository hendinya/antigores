@extends('layouts.app')

@section('content')
    <style>
        #adminProductsHeaderActions,
        #adminProductsPaginationControls,
        .admin-product-actions,
        #adminImportActions,
        #adminBulkActions {
            gap: .5rem;
        }
        #adminProductsFilterForm .form-label {
            margin-bottom: .35rem;
        }
        #adminProductsHeader .h5 {
            line-height: 1.25;
        }
        #adminProductsFilterForm .form-control,
        #adminProductsFilterForm .form-select {
            font-size: .95rem;
        }
        #adminProductsTableBody td {
            word-break: break-word;
        }
        #adminProductsHeader .btn,
        #adminProductsPaginationControls .btn,
        #adminImportActions .btn,
        .admin-product-actions .btn {
            min-height: 44px;
        }
        #adminProductsFilterForm .form-control::placeholder {
            font-size: .9rem;
        }
        #adminProductsFilterForm .btn,
        #adminImportActions .btn {
            padding-left: .8rem;
            padding-right: .8rem;
        }
        .affiliator-visibility-cell {
            text-align: center;
        }
        #adminProductsPaginationInfo {
            line-height: 1.3;
        }
        @media (min-width: 768px) and (max-width: 1023.98px) {
            #adminProductsHeaderActions {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
            }
            #adminProductsFilterForm .col-md-3 {
                width: 50%;
            }
            #adminImportActions {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (min-width: 1024px) and (max-width: 1199.98px) {
            #adminProductsFilterForm .col-md-3 {
                width: 50%;
            }
        }
        @media (max-width: 767.98px) {
            #adminProductsHeader {
                flex-direction: column;
                align-items: stretch !important;
            }
            #adminProductsHeader .h5 {
                font-size: 1.02rem;
            }
            #adminProductsHeaderActions {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
            }
            #adminProductsHeaderActions .btn,
            #adminImportActions .btn,
            .admin-product-actions .btn {
                width: 100%;
            }
            #adminProductsHeaderActions .btn {
                white-space: normal;
                line-height: 1.2;
            }
            #adminProductsFilterForm .col-md-3,
            #adminProductsFilterForm .col-12 {
                width: 100%;
            }
            #adminProductsFilterForm .form-label {
                font-size: .83rem;
            }
            #adminImportActions {
                width: 100%;
                display: grid !important;
                grid-template-columns: 1fr 1fr;
            }
            #adminProductsPaginationWrap {
                flex-direction: column;
                align-items: stretch !important;
                gap: .75rem;
            }
            #adminProductsPaginationControls {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
            }
            #adminProductsPaginationInfo {
                font-size: .82rem;
            }
            .admin-product-actions {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                width: 100%;
            }
            .admin-product-actions form {
                width: 100%;
            }
            .admin-product-actions form .btn {
                width: 100%;
            }
        }
        @media (max-width: 374.98px) {
            #adminProductsHeaderActions,
            #adminImportActions,
            #adminProductsPaginationControls,
            .admin-product-actions {
                grid-template-columns: 1fr;
            }
            #adminProductsHeaderActions .btn,
            #adminImportActions .btn {
                font-size: .83rem;
            }
            #adminProductsHeader .h5 {
                font-size: .96rem;
            }
            #adminProductsFilterForm .form-control,
            #adminProductsFilterForm .form-select {
                font-size: .9rem;
            }
        }
        @media (min-width: 375px) and (max-width: 413.98px) {
            #adminProductsPaginationInfo {
                font-size: .85rem;
            }
        }
        @media (min-width: 414px) and (max-width: 575.98px) {
            #adminProductsHeader .h5 {
                font-size: 1.05rem;
            }
        }
    </style>
    <div id="adminProductsHeader" class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Produk Antigores</h1>
        <div id="adminProductsHeaderActions" class="d-flex">
            <a href="{{ route('admin.products.template') }}" class="btn btn-outline-secondary btn-sm">Download Template Excel</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-dark btn-sm">Tambah</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form id="adminProductsFilterForm" method="GET" action="{{ route('admin.products.index') }}" data-search-url="{{ route('admin.products.search') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Cari Nama</label>
                    <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="Cari nama produk / ukuran antigores">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select id="filter_category_id" name="category_id" class="form-select">
                        <option value="">Semua kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Brand</label>
                    <select id="filter_brand_id" name="brand_id" class="form-select">
                        <option value="">Semua brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) request('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Etalase</label>
                    <select name="phone_type_id" class="form-select">
                        <option value="">Semua etalase</option>
                        @foreach($phoneTypes as $phoneType)
                            <option value="{{ $phoneType->id }}" @selected((string) request('phone_type_id') === (string) $phoneType->id)>{{ $phoneType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-dark btn-sm">Filter</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label class="form-label">Import Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx" required>
                </div>
                <div class="col-md-4 d-flex" id="adminImportActions">
                    <button name="preview" value="1" class="btn btn-outline-dark btn-sm">Preview</button>
                    <button class="btn btn-dark btn-sm">Import Langsung</button>
                </div>
            </form>
        </div>
    </div>

    @php
        $previewRows = session('import_preview_rows', []);
    @endphp
    @if($previewRows)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Preview Import (Dry-Run)</span>
                <form method="POST" action="{{ route('admin.products.import') }}">
                    @csrf
                    <input type="hidden" name="commit_preview" value="1">
                    <button class="btn btn-dark btn-sm">Simpan Hasil Preview</button>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th>Baris</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Brand</th>
                        <th>Etalase</th>
                        <th>Catatan Produk</th>
                        <th>Status</th>
                        <th>Pesan</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($previewRows as $row)
                        <tr>
                            <td>{{ $row['line'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['brand'] }}</td>
                            <td>{{ $row['showcase'] }}</td>
                            <td>{{ $row['product_note'] !== '' ? $row['product_note'] : '-' }}</td>
                            <td>
                                @if($row['status'] === 'valid')
                                    <span class="badge text-bg-success">valid</span>
                                @elseif($row['status'] === 'duplicate')
                                    <span class="badge text-bg-warning">duplicate</span>
                                @else
                                    <span class="badge text-bg-danger">error</span>
                                @endif
                            </td>
                            <td>{{ $row['message'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    <form id="adminBulkActionForm" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="_method" id="adminBulkMethodInput" value="POST">
        <input type="hidden" name="is_visible_for_affiliator" id="adminBulkVisibilityInput" value="">
        <input type="hidden" name="redirect_to" id="adminBulkRedirectToInput" value="{{ url()->full() }}">
        <div id="adminBulkIdsWrap"></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center" id="adminBulkActionsWrap">
            <label class="d-md-none d-flex align-items-center gap-2 mb-0 small text-secondary">
                <input type="checkbox" id="adminBulkSelectAllMobile" aria-label="Pilih semua produk (mobile)">
                <span>Pilih semua</span>
            </label>
            <div class="small text-secondary" id="adminBulkSelectedInfo">0 produk dipilih</div>
            <div class="d-flex d-none" id="adminBulkActions">
                <button type="button" class="btn btn-outline-success btn-sm" id="adminBulkShowBtn" disabled>Tampilkan Affiliator</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="adminBulkHideBtn" disabled>Sembunyikan Affiliator</button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="adminBulkDeleteBtn" disabled>Hapus Terpilih</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th style="width: 56px;">
                        <input type="checkbox" id="adminBulkSelectAll" aria-label="Pilih semua produk">
                        <span class="small ms-1">Pilih</span>
                    </th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Bentuk Kamera</th>
                    <th>Ukuran Antigores</th>
                    <th>Etalase</th>
                    <th>Brand</th>
                    <th>Catatan Produk</th>
                    <th class="text-center">Tampilkan untuk Affiliator</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody id="adminProductsTableBody">
                @forelse($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="bulk-product-checkbox" value="{{ $product->id }}" aria-label="Pilih produk {{ $product->name }}">
                        </td>
                        <td>
                            @if($product->category->image_path)
                                <img src="{{ asset('storage/'.$product->category->image_path) }}" alt="{{ $product->category->name }}" class="rounded" style="width: 42px; height: 42px; object-fit: cover;">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->phoneType->camera_shape ?? '-' }}</td>
                        <td>{{ $product->phoneType->antigores_size ?? '-' }}</td>
                        <td>{{ $product->phoneType->name }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($product->brand->image_path)
                                    <img src="{{ asset('storage/'.$product->brand->image_path) }}" alt="{{ $product->brand->name }}" class="rounded" style="width: 28px; height: 28px; object-fit: cover;">
                                @endif
                                <span>{{ $product->brand->name }}</span>
                            </div>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($product->product_note, 80) ?: '-' }}</td>
                        <td class="affiliator-visibility-cell">
                            <form method="POST" action="{{ route('admin.products.visibility', $product) }}" class="d-inline" data-confirm-visibility>
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_visible_for_affiliator" value="{{ $product->is_visible_for_affiliator ? 1 : 0 }}">
                                <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                <label class="uiverse-switch">
                                    <input class="visibility-switch" type="checkbox" role="switch" @checked($product->is_visible_for_affiliator)>
                                    <span class="uiverse-slider"></span>
                                </label>
                            </form>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex admin-product-actions">
                                <a href="{{ route('admin.products.edit', ['product' => $product, 'return_to' => url()->full()]) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" data-confirm-delete>
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                    <button class="btn btn-outline-danger btn-sm btn-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center py-4 text-secondary">Belum ada produk.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3 d-flex justify-content-between align-items-center" id="adminProductsPaginationWrap">
        <small class="text-secondary" id="adminProductsPaginationInfo">Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} data</small>
        <div class="d-flex gap-2" id="adminProductsPaginationControls">
            <button class="btn btn-outline-secondary btn-sm" id="adminPrevPage" disabled>Sebelumnya</button>
            <button class="btn btn-outline-secondary btn-sm" id="adminNextPage" @disabled(!$products->hasMorePages())>Berikutnya</button>
        </div>
    </div>
    <script>
        (() => {
            const filterForm = document.getElementById('adminProductsFilterForm');
            const searchUrl = filterForm.dataset.searchUrl;
            const keywordInput = filterForm.querySelector('input[name="keyword"]');
            const categorySelect = document.getElementById('filter_category_id');
            const brandSelect = document.getElementById('filter_brand_id');
            const showcaseSelect = filterForm.querySelector('select[name="phone_type_id"]');
            const tableBody = document.getElementById('adminProductsTableBody');
            const paginationInfo = document.getElementById('adminProductsPaginationInfo');
            const prevButton = document.getElementById('adminPrevPage');
            const nextButton = document.getElementById('adminNextPage');
            const bulkSelectAll = document.getElementById('adminBulkSelectAll');
            const bulkSelectAllMobile = document.getElementById('adminBulkSelectAllMobile');
            const bulkSelectedInfo = document.getElementById('adminBulkSelectedInfo');
            const bulkActions = document.getElementById('adminBulkActions');
            const bulkDeleteButton = document.getElementById('adminBulkDeleteBtn');
            const bulkShowButton = document.getElementById('adminBulkShowBtn');
            const bulkHideButton = document.getElementById('adminBulkHideBtn');
            const bulkForm = document.getElementById('adminBulkActionForm');
            const bulkMethodInput = document.getElementById('adminBulkMethodInput');
            const bulkVisibilityInput = document.getElementById('adminBulkVisibilityInput');
            const bulkRedirectToInput = document.getElementById('adminBulkRedirectToInput');
            const bulkIdsWrap = document.getElementById('adminBulkIdsWrap');
            const csrfToken = '{{ csrf_token() }}';
            let debounceTimer;
            let currentPage = {{ $products->currentPage() }};

            const getSelectedIds = () => Array.from(tableBody.querySelectorAll('.bulk-product-checkbox:checked'))
                .map((checkbox) => checkbox.value);

            const updateBulkSelectionState = () => {
                const allCheckboxes = Array.from(tableBody.querySelectorAll('.bulk-product-checkbox'));
                const selectedIds = getSelectedIds();
                const hasSelection = selectedIds.length > 0;

                bulkSelectedInfo.textContent = `${selectedIds.length} produk dipilih`;
                bulkActions.classList.toggle('d-none', !hasSelection);
                bulkDeleteButton.disabled = !hasSelection;
                bulkShowButton.disabled = !hasSelection;
                bulkHideButton.disabled = !hasSelection;

                if (!allCheckboxes.length) {
                    bulkSelectAll.checked = false;
                    bulkSelectAll.indeterminate = false;
                    return;
                }

                const selectedCount = allCheckboxes.filter((checkbox) => checkbox.checked).length;
                bulkSelectAll.checked = selectedCount > 0 && selectedCount === allCheckboxes.length;
                bulkSelectAll.indeterminate = selectedCount > 0 && selectedCount < allCheckboxes.length;
                if (bulkSelectAllMobile) {
                    bulkSelectAllMobile.checked = bulkSelectAll.checked;
                    bulkSelectAllMobile.indeterminate = bulkSelectAll.indeterminate;
                }
            };

            const submitBulkAction = async ({ actionUrl, method, visibility, confirmationText, successText }) => {
                const selectedIds = getSelectedIds();
                if (!selectedIds.length) {
                    return;
                }

                const confirmation = await Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Aksi Massal',
                    text: confirmationText,
                    showCancelButton: true,
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Tidak',
                    reverseButtons: true,
                });
                if (!confirmation.isConfirmed) {
                    return;
                }

                bulkForm.setAttribute('action', actionUrl);
                bulkMethodInput.value = method;
                bulkVisibilityInput.value = visibility ?? '';
                bulkRedirectToInput.value = `${window.location.pathname}${window.location.search}`;
                bulkIdsWrap.innerHTML = selectedIds.map((id) => `<input type="hidden" name="product_ids[]" value="${id}">`).join('');
                bulkForm.submit();
            };

            const renderRows = (items) => {
                if (!items.length) {
                    tableBody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-secondary">Belum ada produk.</td></tr>';
                    updateBulkSelectionState();
                    return;
                }

                tableBody.innerHTML = items.map((item) => `
                    <tr>
                        <td>
                            <input type="checkbox" class="bulk-product-checkbox" value="${item.id}" aria-label="Pilih produk ${item.name}">
                        </td>
                        <td>
                            ${item.category_image ? `<img src="${item.category_image}" alt="${item.category}" class="rounded" style="width: 42px; height: 42px; object-fit: cover;">` : '-'}
                        </td>
                        <td>${item.name}</td>
                        <td>${item.camera_shape ?? '-'}</td>
                        <td>${item.antigores_size ?? '-'}</td>
                        <td>${item.showcase}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                ${item.brand_image ? `<img src="${item.brand_image}" alt="${item.brand}" class="rounded" style="width: 28px; height: 28px; object-fit: cover;">` : ''}
                                <span>${item.brand}</span>
                            </div>
                        </td>
                        <td>${item.product_note ? item.product_note : '-'}</td>
                        <td class="affiliator-visibility-cell">
                            <form method="POST" action="${item.visibility_url}" class="d-inline" data-confirm-visibility>
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="PATCH">
                                <input type="hidden" name="is_visible_for_affiliator" value="${item.is_visible_for_affiliator ? 1 : 0}">
                                <input type="hidden" name="redirect_to" value="${window.location.pathname}${window.location.search}">
                                <label class="uiverse-switch">
                                    <input class="visibility-switch" type="checkbox" role="switch" ${item.is_visible_for_affiliator ? 'checked' : ''}>
                                    <span class="uiverse-slider"></span>
                                </label>
                            </form>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex admin-product-actions">
                                <a href="${item.edit_url}${item.edit_url.includes('?') ? '&' : '?'}return_to=${encodeURIComponent(window.location.pathname + window.location.search)}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <form method="POST" action="${item.delete_url}" class="d-inline" data-confirm-delete>
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="redirect_to" value="${window.location.pathname}${window.location.search}">
                                    <button class="btn btn-outline-danger btn-sm btn-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                `).join('');
                updateBulkSelectionState();
            };

            const updatePagination = (pagination) => {
                const from = pagination.from ?? 0;
                const to = pagination.to ?? 0;
                paginationInfo.textContent = `Menampilkan ${from} - ${to} dari ${pagination.total} data`;
                prevButton.disabled = pagination.current_page <= 1;
                nextButton.disabled = pagination.current_page >= pagination.last_page;
                currentPage = pagination.current_page;
            };

            const loadProducts = async (page = 1) => {
                const params = new URLSearchParams({
                    keyword: keywordInput.value.trim(),
                    category_id: categorySelect.value,
                    brand_id: brandSelect.value,
                    phone_type_id: showcaseSelect.value,
                    page: String(page),
                });
                const nextUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.replaceState({}, '', nextUrl);

                const response = await fetch(`${searchUrl}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                renderRows(payload.items);
                updatePagination(payload.pagination);
            };

            const triggerRealtimeFilter = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadProducts(1);
                }, 300);
            };

            categorySelect.addEventListener('change', triggerRealtimeFilter);
            brandSelect.addEventListener('change', triggerRealtimeFilter);
            showcaseSelect.addEventListener('change', triggerRealtimeFilter);
            keywordInput.addEventListener('input', triggerRealtimeFilter);
            prevButton.addEventListener('click', () => loadProducts(currentPage - 1));
            nextButton.addEventListener('click', () => loadProducts(currentPage + 1));
            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                loadProducts(1);
            });
            bulkSelectAll.addEventListener('change', () => {
                tableBody.querySelectorAll('.bulk-product-checkbox').forEach((checkbox) => {
                    checkbox.checked = bulkSelectAll.checked;
                });
                updateBulkSelectionState();
            });
            if (bulkSelectAllMobile) {
                bulkSelectAllMobile.addEventListener('change', () => {
                    tableBody.querySelectorAll('.bulk-product-checkbox').forEach((checkbox) => {
                        checkbox.checked = bulkSelectAllMobile.checked;
                    });
                    updateBulkSelectionState();
                });
            }
            bulkDeleteButton.addEventListener('click', () => submitBulkAction({
                actionUrl: '{{ route('admin.products.bulk-delete') }}',
                method: 'POST',
                confirmationText: 'Yakin ingin menghapus semua produk yang dipilih?',
            }));
            bulkShowButton.addEventListener('click', () => submitBulkAction({
                actionUrl: '{{ route('admin.products.bulk-visibility') }}',
                method: 'PATCH',
                visibility: '1',
                confirmationText: 'Yakin ingin menampilkan produk terpilih untuk affiliator?',
            }));
            bulkHideButton.addEventListener('click', () => submitBulkAction({
                actionUrl: '{{ route('admin.products.bulk-visibility') }}',
                method: 'PATCH',
                visibility: '0',
                confirmationText: 'Yakin ingin menyembunyikan produk terpilih dari affiliator?',
            }));
            tableBody.addEventListener('change', async (event) => {
                const target = event.target;
                if (target instanceof HTMLInputElement && target.classList.contains('bulk-product-checkbox')) {
                    updateBulkSelectionState();
                    return;
                }
                if (!(target instanceof HTMLInputElement) || !target.classList.contains('visibility-switch')) {
                    return;
                }
                const form = target.closest('form[data-confirm-visibility]');
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }
                const hiddenInput = form.querySelector('input[name="is_visible_for_affiliator"]');
                if (!(hiddenInput instanceof HTMLInputElement)) {
                    return;
                }
                hiddenInput.value = target.checked ? '1' : '0';
                const nextStateLabel = target.checked ? 'menampilkan' : 'menyembunyikan';
                const confirmation = await Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Perubahan',
                    text: `Apakah Anda yakin ingin ${nextStateLabel} produk ini di halaman affiliator?`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Tidak',
                    reverseButtons: true,
                });
                if (!confirmation.isConfirmed) {
                    target.checked = !target.checked;
                    hiddenInput.value = target.checked ? '1' : '0';
                    return;
                }
                form.submit();
            });
            updateBulkSelectionState();
        })();
    </script>
@endsection
