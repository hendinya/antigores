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
        #adminProductsTopToolbar {
            background: #f6f8fc;
            border-radius: 999px;
            padding: .55rem;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: .45rem;
        }
        #adminProductsTopToolbar .toolbar-btn {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            border: 0;
            background: #eef2f8;
            color: #475467;
            font-size: 1.4rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        #adminProductsTopToolbar .toolbar-btn.btn-add {
            background: #00cc45;
            color: #fff;
            font-size: 2rem;
        }
        #adminProductsKeywordWrap {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            min-height: 48px;
            display: flex;
            align-items: center;
            padding: 0 .65rem;
            gap: .5rem;
        }
        #adminProductsKeywordWrap .keyword-spacer {
            flex: 1 1 auto;
        }
        #adminProductsKeywordWrap .keyword-inline-btn {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 0;
            background: #f2f5fb;
            color: #475467;
            font-size: 1.35rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        #adminProductsKeywordWrap .keyword-inline-btn.d-none {
            display: none !important;
        }
        #adminProductsKeywordWrap .keyword-icon {
            color: #98a2b3;
            font-size: 1.25rem;
        }
        #adminProductsKeywordWrap input {
            border: 0 !important;
            box-shadow: none !important;
            padding: 0;
            font-size: 1.1rem;
        }
        #adminProductsAdvancedPanel {
            border: 1px solid #e5ebf4;
            border-radius: .9rem;
            padding: .85rem;
            background: #fff;
        }
        #adminProductsAdvancedPanel.d-none {
            display: none !important;
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
            #adminProductsTableBody td::before {
                content: none !important;
                display: none !important;
            }
            #adminProductsTableBody tr {
                display: grid;
                grid-template-columns: 56px 1fr auto 28px;
                gap: .6rem .75rem;
                border: 1px solid #e5ebf4;
                border-radius: .9rem;
                padding: .85rem;
                margin: .75rem;
                background: #fff;
                overflow: visible;
            }
            #adminProductsTableBody .product-cell-camera,
            #adminProductsTableBody .product-cell-size,
            #adminProductsTableBody .product-cell-showcase,
            #adminProductsTableBody .product-cell-brand,
            #adminProductsTableBody .product-cell-note,
            #adminProductsTableBody .product-cell-status {
                display: none;
            }
            #adminProductsTableBody .product-cell-select {
                grid-column: 3;
                grid-row: 1;
                align-self: start;
                text-align: right;
                padding: 0;
                margin-top: .15rem;
            }
            #adminProductsTableBody .product-cell-image {
                grid-column: 1;
                grid-row: 1 / span 2;
                padding: 0;
            }
            #adminProductsTableBody .product-cell-name {
                grid-column: 2;
                grid-row: 1;
                padding: 0;
            }
            #adminProductsTableBody .product-cell-visibility {
                grid-column: 1 / -1;
                grid-row: 2;
                padding: 0;
                text-align: left;
                border-top: 1px dashed #e8edf5;
                padding-top: .65rem;
                margin-top: .2rem;
            }
            #adminProductsTableBody .product-cell-actions {
                grid-column: 4;
                grid-row: 1;
                padding: 0;
                text-align: right;
                align-self: start;
            }
            #adminProductsTableBody .product-cell-actions .admin-product-actions-desktop {
                display: none !important;
            }
            .admin-mobile-actions {
                position: relative;
                display: inline-block;
            }
            .admin-mobile-actions summary {
                list-style: none;
                cursor: pointer;
                user-select: none;
                width: 24px;
                height: 24px;
                border-radius: .35rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #4b5563;
                font-size: 20px;
                line-height: 1;
            }
            .admin-mobile-actions summary::-webkit-details-marker {
                display: none;
            }
            .admin-mobile-actions[open] summary {
                background: #f2f5fb;
            }
            .admin-mobile-actions-menu {
                position: absolute;
                top: calc(100% + 6px);
                right: 0;
                width: 165px;
                border: 1px solid #dbe4f0;
                border-radius: .65rem;
                background: #fff;
                box-shadow: 0 .4rem 1.2rem rgba(25, 35, 52, .12);
                padding: .45rem;
                z-index: 20;
                display: grid;
                gap: .35rem;
            }
            .admin-mobile-actions-menu .btn {
                width: 100%;
                min-height: 38px;
                font-size: .86rem;
                padding-top: .35rem;
                padding-bottom: .35rem;
            }
            .admin-mobile-actions-menu form {
                margin: 0;
            }
            .product-mobile-visibility-label {
                font-size: .82rem;
                color: #6b7280;
                margin-left: .5rem;
            }
            .product-name-title {
                font-size: 1.15rem;
                font-weight: 700;
                color: #3f4ad1;
                line-height: 1.25;
            }
            .product-name-sub {
                color: #6b7280;
                margin-top: .2rem;
            }
            .product-mobile-badges {
                display: flex;
                flex-wrap: wrap;
                gap: .45rem;
                margin-top: .55rem;
            }
            .product-mobile-badge {
                display: inline-block;
                border-radius: .6rem;
                padding: .34rem .62rem;
                font-size: .84rem;
                line-height: 1.2;
                background: #f4edff;
                color: #5b2db6;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .product-mobile-badge-outline {
                background: #fff;
                border: 1px solid #e7d9ff;
            }
            #adminProductsTableBody .product-cell-visibility .uiverse-switch {
                margin-top: .2rem;
            }
        }
        @media (min-width: 768px) {
            .admin-mobile-actions {
                display: none !important;
            }
            .product-mobile-visibility-label {
                display: none !important;
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
    <div class="mb-3">
        <form id="adminProductsFilterForm" method="GET" action="{{ route('admin.products.index') }}" data-search-url="{{ route('admin.products.search') }}">
            <div id="adminProductsTopToolbar">
                <div id="adminProductsKeywordWrap">
                    <span class="keyword-icon">🔎</span>
                    <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="Cari nama produk / ukuran antigores">
                    <span class="keyword-spacer"></span>
                    <button type="button" class="keyword-inline-btn d-none" id="adminProductsClearKeywordBtn" aria-label="Reset keyword">✕</button>
                    <button type="button" class="keyword-inline-btn" id="adminProductsTogglePanelBtn" aria-label="Buka panel filter">☰</button>
                </div>
                <a href="{{ route('admin.products.create') }}" class="toolbar-btn btn-add" aria-label="Tambah produk">+</a>
            </div>
            <div id="adminProductsAdvancedPanel" class="mt-2 d-none">
                <div class="row g-2 align-items-end">
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
                    <div class="col-md-3">
                        <label class="form-label">Status Presisi</label>
                        <select name="precision_status" class="form-select">
                            <option value="">Semua status</option>
                            @foreach($precisionStatuses as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected((string) request('precision_status') === (string) $statusValue)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-dark btn-sm">Filter</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        <a href="{{ route('admin.products.template') }}" class="btn btn-outline-secondary btn-sm ms-auto">Download Template</a>
                        <a href="{{ route('admin.products.export-filtered', request()->query()) }}" class="btn btn-outline-dark btn-sm" id="adminExportFilteredBtn" data-base-export-url="{{ route('admin.products.export-filtered') }}">Download Data Filter</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm mb-3 d-none" id="adminProductsImportPanel">
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
                        <th>Status Presisi</th>
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
                            <td>{{ $row['precision_status'] ?? '-' }}</td>
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
                    <th>Status Presisi</th>
                    <th class="text-center">Tampilkan untuk Affiliator</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody id="adminProductsTableBody">
                @forelse($products as $product)
                    @php
                        $variants = $product->master?->variants ?? collect([$product]);
                        $showcaseNames = $variants->pluck('phoneType.name')->filter()->unique()->values();
                        $categoryImages = $variants
                            ->map(fn($variant) => ['name' => $variant->category->name ?? '-', 'url' => $variant->category?->image_path ? asset('storage/'.$variant->category->image_path) : null])
                            ->unique(fn($item) => ($item['name'] ?? '').'|'.($item['url'] ?? ''))
                            ->values();
                    @endphp
                    <tr>
                        <td class="product-cell-select">
                            <input type="checkbox" class="bulk-product-checkbox" value="{{ $product->id }}" aria-label="Pilih produk {{ $product->name }}">
                        </td>
                        <td class="product-cell-image">
                            @if($categoryImages->isNotEmpty())
                                <div class="d-flex gap-1 flex-wrap">
                                    @foreach($categoryImages as $categoryImage)
                                        @if($categoryImage['url'])
                                            <img src="{{ $categoryImage['url'] }}" alt="{{ $categoryImage['name'] }}" class="rounded" style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <span class="badge text-bg-light">{{ $categoryImage['name'] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="product-cell-name">
                            <div class="product-name-title">{{ $product->name }}</div>
                            <div class="product-name-sub d-md-none">{{ $product->phoneType->camera_shape ?? '-' }} {{ $product->phoneType->antigores_size ?? '-' }}</div>
                            <div class="product-mobile-badges d-md-none">
                                <span class="product-mobile-badge">{{ \Illuminate\Support\Str::limit($product->product_note, 40) ?: '-' }}</span>
                                <span class="product-mobile-badge product-mobile-badge-outline">{{ $showcaseNames->implode(', ') ?: '-' }}</span>
                                <span class="product-mobile-badge product-mobile-badge-outline">{{ \App\Models\ProductMaster::precisionStatusLabel($product->master?->precision_status ?? $product->precision_status) }}</span>
                            </div>
                        </td>
                        <td class="product-cell-camera">{{ $product->phoneType->camera_shape ?? '-' }}</td>
                        <td class="product-cell-size">{{ $product->phoneType->antigores_size ?? '-' }}</td>
                        <td class="product-cell-showcase">{{ $showcaseNames->implode(', ') ?: '-' }}</td>
                        <td class="product-cell-brand">
                            <div class="d-flex align-items-center gap-2">
                                @if($product->brand->image_path)
                                    <img src="{{ asset('storage/'.$product->brand->image_path) }}" alt="{{ $product->brand->name }}" class="rounded" style="width: 28px; height: 28px; object-fit: cover;">
                                @endif
                                <span>{{ $product->brand->name }}</span>
                            </div>
                        </td>
                        <td class="product-cell-note">{{ \Illuminate\Support\Str::limit($product->product_note, 80) ?: '-' }}</td>
                        <td class="product-cell-status">{{ \App\Models\ProductMaster::precisionStatusLabel($product->master?->precision_status ?? $product->precision_status) }}</td>
                        <td class="affiliator-visibility-cell product-cell-visibility">
                            <form method="POST" action="{{ route('admin.products.visibility', $product) }}" class="d-inline" data-confirm-visibility>
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_visible_for_affiliator" value="{{ $product->is_visible_for_affiliator ? 1 : 0 }}">
                                <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                <label class="uiverse-switch">
                                    <input class="visibility-switch" type="checkbox" role="switch" @checked($product->is_visible_for_affiliator)>
                                    <span class="uiverse-slider"></span>
                                </label>
                                <span class="product-mobile-visibility-label">Tampilkan untuk Affiliator</span>
                            </form>
                        </td>
                        <td class="text-end product-cell-actions">
                            <div class="d-inline-flex admin-product-actions admin-product-actions-desktop">
                                <a href="{{ route('admin.products.create', ['source_product_id' => $product->id, 'return_to' => url()->full()]) }}" class="btn btn-outline-dark btn-sm">Salin</a>
                                <a href="{{ route('admin.products.edit', ['product' => $product, 'return_to' => url()->full()]) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" data-confirm-delete>
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                    <button class="btn btn-outline-danger btn-sm btn-delete">Delete</button>
                                </form>
                            </div>
                            <details class="admin-mobile-actions">
                                <summary aria-label="Aksi Produk">⋮</summary>
                                <div class="admin-mobile-actions-menu">
                                    <a href="{{ route('admin.products.create', ['source_product_id' => $product->id, 'return_to' => url()->full()]) }}" class="btn btn-outline-dark btn-sm">Salin</a>
                                    <a href="{{ route('admin.products.edit', ['product' => $product, 'return_to' => url()->full()]) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm-delete>
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                        <button class="btn btn-outline-danger btn-sm btn-delete">Delete</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center py-4 text-secondary">Belum ada produk.</td></tr>
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
            const precisionStatusSelect = filterForm.querySelector('select[name="precision_status"]');
            const clearKeywordButton = document.getElementById('adminProductsClearKeywordBtn');
            const togglePanelButton = document.getElementById('adminProductsTogglePanelBtn');
            const exportFilteredButton = document.getElementById('adminExportFilteredBtn');
            const advancedPanel = document.getElementById('adminProductsAdvancedPanel');
            const importPanel = document.getElementById('adminProductsImportPanel');
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
                    tableBody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-secondary">Belum ada produk.</td></tr>';
                    updateBulkSelectionState();
                    return;
                }

                tableBody.innerHTML = items.map((item) => `
                    <tr>
                        <td class="product-cell-select">
                            <input type="checkbox" class="bulk-product-checkbox" value="${item.id}" aria-label="Pilih produk ${item.name}">
                        </td>
                        <td class="product-cell-image">
                            ${Array.isArray(item.category_images) && item.category_images.length
                                ? `<div class="d-flex gap-1 flex-wrap">${item.category_images.map((image) => image.url
                                    ? `<img src="${image.url}" alt="${image.name}" class="rounded" style="width: 32px; height: 32px; object-fit: cover;">`
                                    : `<span class="badge text-bg-light">${image.name}</span>`).join('')}</div>`
                                : '-'}
                        </td>
                        <td class="product-cell-name">
                            <div class="product-name-title">${item.name}</div>
                            <div class="product-name-sub d-md-none">${item.camera_shape ?? '-'} ${item.antigores_size ?? '-'}</div>
                            <div class="product-mobile-badges d-md-none">
                                <span class="product-mobile-badge">${item.product_note ? item.product_note : '-'}</span>
                                <span class="product-mobile-badge product-mobile-badge-outline">${item.showcase ? item.showcase : '-'}</span>
                                <span class="product-mobile-badge product-mobile-badge-outline">${item.precision_status_label ? item.precision_status_label : '-'}</span>
                            </div>
                        </td>
                        <td class="product-cell-camera">${item.camera_shape ?? '-'}</td>
                        <td class="product-cell-size">${item.antigores_size ?? '-'}</td>
                        <td class="product-cell-showcase">${item.showcase}</td>
                        <td class="product-cell-brand">
                            <div class="d-flex align-items-center gap-2">
                                ${item.brand_image ? `<img src="${item.brand_image}" alt="${item.brand}" class="rounded" style="width: 28px; height: 28px; object-fit: cover;">` : ''}
                                <span>${item.brand}</span>
                            </div>
                        </td>
                        <td class="product-cell-note">${item.product_note ? item.product_note : '-'}</td>
                        <td class="product-cell-status">${item.precision_status_label ? item.precision_status_label : '-'}</td>
                        <td class="affiliator-visibility-cell product-cell-visibility">
                            <form method="POST" action="${item.visibility_url}" class="d-inline" data-confirm-visibility>
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="PATCH">
                                <input type="hidden" name="is_visible_for_affiliator" value="${item.is_visible_for_affiliator ? 1 : 0}">
                                <input type="hidden" name="redirect_to" value="${window.location.pathname}${window.location.search}">
                                <label class="uiverse-switch">
                                    <input class="visibility-switch" type="checkbox" role="switch" ${item.is_visible_for_affiliator ? 'checked' : ''}>
                                    <span class="uiverse-slider"></span>
                                </label>
                                <span class="product-mobile-visibility-label">Tampilkan untuk Affiliator</span>
                            </form>
                        </td>
                        <td class="text-end product-cell-actions">
                            <div class="d-inline-flex admin-product-actions admin-product-actions-desktop">
                                <a href="{{ route('admin.products.create') }}?source_product_id=${item.id}&return_to=${encodeURIComponent(window.location.pathname + window.location.search)}" class="btn btn-outline-dark btn-sm">Salin</a>
                                <a href="${item.edit_url}${item.edit_url.includes('?') ? '&' : '?'}return_to=${encodeURIComponent(window.location.pathname + window.location.search)}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <form method="POST" action="${item.delete_url}" class="d-inline" data-confirm-delete>
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="redirect_to" value="${window.location.pathname}${window.location.search}">
                                    <button class="btn btn-outline-danger btn-sm btn-delete">Delete</button>
                                </form>
                            </div>
                            <details class="admin-mobile-actions">
                                <summary aria-label="Aksi Produk">⋮</summary>
                                <div class="admin-mobile-actions-menu">
                                    <a href="{{ route('admin.products.create') }}?source_product_id=${item.id}&return_to=${encodeURIComponent(window.location.pathname + window.location.search)}" class="btn btn-outline-dark btn-sm">Salin</a>
                                    <a href="${item.edit_url}${item.edit_url.includes('?') ? '&' : '?'}return_to=${encodeURIComponent(window.location.pathname + window.location.search)}" class="btn btn-outline-primary btn-sm">Edit</a>
                                    <form method="POST" action="${item.delete_url}" data-confirm-delete>
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="redirect_to" value="${window.location.pathname}${window.location.search}">
                                        <button class="btn btn-outline-danger btn-sm btn-delete">Delete</button>
                                    </form>
                                </div>
                            </details>
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

            const buildFilterParams = (page = null) => {
                const params = new URLSearchParams({
                    keyword: keywordInput.value.trim(),
                    category_id: categorySelect.value,
                    brand_id: brandSelect.value,
                    phone_type_id: showcaseSelect.value,
                    precision_status: precisionStatusSelect ? precisionStatusSelect.value : '',
                });

                if (page !== null) {
                    params.set('page', String(page));
                }

                return params;
            };

            const syncExportFilteredUrl = () => {
                if (!(exportFilteredButton instanceof HTMLAnchorElement)) {
                    return;
                }
                const params = buildFilterParams(null);
                exportFilteredButton.href = `${exportFilteredButton.dataset.baseExportUrl}?${params.toString()}`;
            };

            const loadProducts = async (page = 1) => {
                const params = buildFilterParams(page);
                const nextUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.replaceState({}, '', nextUrl);
                syncExportFilteredUrl();

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
            const syncKeywordActionButtons = () => {
                clearKeywordButton.classList.toggle('d-none', keywordInput.value.trim().length === 0);
            };

            const setPanelVisibility = (isVisible) => {
                advancedPanel.classList.toggle('d-none', !isVisible);
                importPanel.classList.toggle('d-none', !isVisible);
            };

            const hasActiveAdvancedFilter = !!(categorySelect.value || brandSelect.value || showcaseSelect.value || (precisionStatusSelect && precisionStatusSelect.value));
            setPanelVisibility(hasActiveAdvancedFilter);

            const handleSelectFilterChange = () => {
                syncExportFilteredUrl();
                triggerRealtimeFilter();
            };

            categorySelect.addEventListener('change', handleSelectFilterChange);
            brandSelect.addEventListener('change', handleSelectFilterChange);
            showcaseSelect.addEventListener('change', handleSelectFilterChange);
            if (precisionStatusSelect) {
                precisionStatusSelect.addEventListener('change', handleSelectFilterChange);
            }
            keywordInput.addEventListener('input', () => {
                syncKeywordActionButtons();
                syncExportFilteredUrl();
                triggerRealtimeFilter();
            });
            clearKeywordButton.addEventListener('click', () => {
                keywordInput.value = '';
                syncKeywordActionButtons();
                syncExportFilteredUrl();
                triggerRealtimeFilter();
                keywordInput.focus();
            });
            togglePanelButton.addEventListener('click', () => {
                const isCurrentlyVisible = !advancedPanel.classList.contains('d-none');
                setPanelVisibility(!isCurrentlyVisible);
            });
            syncKeywordActionButtons();
            syncExportFilteredUrl();
            const bindSelect2RealtimeEvents = () => {
                if (!window.jQuery) {
                    return;
                }
                const bindFor = (element) => {
                    const $el = window.jQuery(element);
                    $el.off('.productsRealtime');
                    $el.on('select2:select.productsRealtime select2:clear.productsRealtime select2:unselect.productsRealtime', handleSelectFilterChange);
                };

                bindFor(categorySelect);
                bindFor(brandSelect);
                bindFor(showcaseSelect);
                if (precisionStatusSelect) {
                    bindFor(precisionStatusSelect);
                }
            };

            bindSelect2RealtimeEvents();
            window.addEventListener('load', bindSelect2RealtimeEvents);
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
