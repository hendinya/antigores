@extends('layouts.app')

@section('content')
    <style>
        #productsSearchControl {
            transition: box-shadow .2s ease, border-color .2s ease;
            border-radius: .5rem;
        }
        #productsSearchControl.is-focused {
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .18);
        }
        #productsVoiceToggle {
            min-width: 46px;
            width: 46px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .15s ease, background-color .2s ease, border-color .2s ease, box-shadow .2s ease, color .2s ease;
            will-change: transform;
        }
        #productsVoiceToggle .mic-icon {
            width: 20px;
            height: 20px;
            display: block;
            transition: transform .2s ease, fill .2s ease;
            fill: currentColor;
        }
        #productsVoiceToggle:hover {
            background: rgba(13, 110, 253, .08);
            border-color: rgba(13, 110, 253, .35);
            color: #0d6efd;
            transform: translateY(-1px);
        }
        #productsVoiceToggle:active {
            transform: scale(.96);
            background: rgba(13, 110, 253, .14);
        }
        #productsVoiceToggle:focus-visible {
            outline: none;
            box-shadow: 0 0 0 .18rem rgba(13, 110, 253, .2);
        }
        #productsVoiceToggle.is-recording {
            color: #fff;
            background: #dc3545;
            border-color: #dc3545;
            animation: productsMicPulse 1.1s ease-in-out infinite;
        }
        #productsVoiceToggle.is-recording .mic-icon {
            transform: scale(1.08);
        }
        #productsVoiceToggle.is-recording::before {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 10px;
            border: 2px solid rgba(220, 53, 69, .45);
            animation: productsMicRing 1.1s ease-out infinite;
        }
        @keyframes productsMicPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }
        @keyframes productsMicRing {
            0% { transform: scale(.85); opacity: .9; }
            100% { transform: scale(1.25); opacity: 0; }
        }
        #brandShortcuts {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: .25rem;
        }
        #brandShortcutsToggle {
            border-color: rgba(255,255,255,.45);
            color: #fff;
            font-weight: 600;
            letter-spacing: .01em;
            border-width: 2px;
            box-shadow: 0 .25rem .75rem rgba(0,0,0,.25);
            transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease, color .18s ease;
        }
        #brandShortcutsToggle:hover {
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.65);
            color: #fff;
        }
        #brandShortcutsToggle[aria-expanded="true"] {
            background: rgba(255,255,255,.16);
            border-color: rgba(255,255,255,.75);
        }
        #brandShortcutsToggle .chevron-icon {
            width: 14px;
            height: 14px;
            transition: transform .18s ease;
            transform-origin: center;
        }
        #brandShortcutsToggle[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
        }
        #productsFiltersToggle {
            min-width: 42px;
        }
        #productsFiltersToggle .chevron-icon {
            width: 16px;
            height: 16px;
            transition: transform .18s ease;
            transform-origin: center;
        }
        #productsFiltersToggle[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
        }
        #productsSearchFields {
            transition: opacity .2s ease;
        }
        #productsSearchControl .input-group > .btn {
            padding-left: .6rem;
            padding-right: .6rem;
        }
        #productsSearchControl .form-control {
            min-height: 46px;
        }
        #productsFiltersPanel .form-label {
            font-size: .84rem;
            margin-bottom: .25rem;
        }
        #productsGrid .card {
            overflow: hidden;
            border-radius: .9rem;
        }
        #productsGrid .card-body {
            padding: .85rem .9rem;
        }
        #productsGrid h2 {
            font-size: .98rem;
            line-height: 1.32;
            min-height: 2.65em;
        }
        #productsGrid p {
            line-height: 1.25;
        }
        #productsGrid .badge {
            font-weight: 600;
        }
        #productsSuggestionWrap {
            border-radius: .75rem !important;
        }
        #productsSuggestionWrap .suggestion-item {
            min-height: 44px;
            font-size: .95rem;
        }
        #productsSuggestionWrap .suggestion-item mark {
            border-radius: .2rem;
        }
        .brand-shortcut-btn {
            border: 1px solid rgba(255,255,255,.2);
            background: rgba(255,255,255,.08);
            color: #fff;
            border-radius: .75rem;
            padding: .35rem .55rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            transition: all .15s ease;
        }
        .brand-shortcut-btn:hover {
            background: rgba(255,255,255,.18);
            border-color: rgba(255,255,255,.35);
            transform: translateY(-1px);
        }
        .brand-shortcut-btn.is-active {
            background: rgba(255,255,255,.26);
            border-color: rgba(255,255,255,.52);
            box-shadow: 0 0 0 .2rem rgba(255,255,255,.14);
        }
        .brand-shortcut-btn img {
            width: 22px;
            height: 22px;
            object-fit: contain;
            background: #fff;
            border-radius: .35rem;
            padding: 2px;
        }
        @media (max-width: 767.98px) {
            #brandShortcuts.brand-dropdown {
                background: rgba(0,0,0,.35);
                backdrop-filter: blur(3px);
                border: 1px solid rgba(255,255,255,.18);
                border-radius: .75rem;
                padding: .6rem;
                box-shadow: 0 .5rem 1rem rgba(0,0,0,.25);
                transition: opacity .22s ease, transform .22s ease, max-height .28s ease, margin .22s ease, padding .22s ease, border-color .22s ease, box-shadow .22s ease;
            }
            #brandShortcuts {
                overflow-x: visible;
                white-space: normal;
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .75rem;
                transform-origin: top center;
                max-height: 360px;
                opacity: 1;
                transform: translateY(0);
            }
            #brandShortcuts.is-collapsed {
                max-height: 0;
                opacity: 0;
                transform: translateY(-8px);
                pointer-events: none;
                overflow: hidden;
                margin-bottom: 0 !important;
                padding-top: 0;
                padding-bottom: 0;
                border-color: transparent;
                box-shadow: none;
            }
            #productsFiltersPanel {
                transition: opacity .22s ease, transform .22s ease, max-height .28s ease, margin .22s ease, padding .22s ease;
                transform-origin: top center;
                max-height: 480px;
                opacity: 1;
                transform: translateY(0);
            }
            #productsFiltersPanel.is-collapsed {
                max-height: 0;
                opacity: 0;
                transform: translateY(-8px);
                pointer-events: none;
                overflow: hidden;
                margin-bottom: 0 !important;
                padding-top: 0;
                padding-bottom: 0;
            }
            .brand-shortcut-btn {
                width: 100%;
                min-height: 96px;
                flex-direction: column;
                justify-content: center;
                text-align: center;
                padding: .7rem .45rem;
                gap: .4rem;
            }
            .brand-shortcut-btn span {
                font-size: .78rem;
                line-height: 1.1;
            }
            .brand-shortcut-btn img {
                width: 38px;
                height: 38px;
            }
            #productsGrid .card-img-top,
            #productsGrid .bg-light {
                height: 220px !important;
            }
            #productsGrid .badge {
                white-space: normal;
                line-height: 1.25;
            }
            #productsSearchFields .col-6 {
                width: 100%;
            }
        }
        @media (max-width: 575.98px) {
            .card-body.p-4 {
                padding: .9rem !important;
            }
            #brandShortcuts {
                gap: .6rem !important;
            }
            #productsGrid .card-body {
                padding: .8rem .82rem;
            }
            #productsGrid h2 {
                font-size: .95rem;
            }
            #productsGrid .col-sm-6 {
                width: 100%;
            }
            #productsGrid .card-img-top,
            #productsGrid .bg-light {
                height: 200px !important;
            }
            #productsSearchControl .input-group {
                flex-wrap: nowrap;
            }
            #productsSuggestionWrap {
                max-height: 220px !important;
            }
            #productsHint {
                font-size: .78rem !important;
            }
            #resultCount {
                font-size: .78rem;
            }
        }
        @media (min-width: 375px) and (max-width: 413.98px) {
            #productsGrid .card-img-top,
            #productsGrid .bg-light {
                height: 210px !important;
            }
            #productsHint {
                font-size: .8rem !important;
            }
        }
        @media (min-width: 414px) and (max-width: 575.98px) {
            #productsGrid .card-img-top,
            #productsGrid .bg-light {
                height: 220px !important;
            }
        }
        @media (max-width: 374.98px) {
            #brandShortcuts {
                gap: .5rem !important;
            }
            .brand-shortcut-btn {
                min-height: 86px;
                padding: .55rem .35rem;
            }
            .brand-shortcut-btn img {
                width: 34px;
                height: 34px;
            }
            .brand-shortcut-btn span {
                font-size: .72rem;
            }
            #productsVoiceToggle,
            #productsFiltersToggle {
                width: 44px;
                min-width: 44px;
                height: 44px;
            }
            #keywordInput {
                font-size: .93rem;
            }
            #productsSuggestionWrap .suggestion-item {
                font-size: .9rem;
            }
            #productsGrid .card-body {
                padding: .75rem .75rem;
            }
            #productsGrid h2 {
                font-size: .92rem;
            }
        }
        @media (min-width: 768px) and (max-width: 1023.98px) {
            #productsGrid .col-lg-4 {
                width: 50%;
            }
            #productsGrid .card-img-top,
            #productsGrid .bg-light {
                height: 250px !important;
            }
            #productsSearchFields .col-lg-6 {
                width: 100%;
            }
        }
        @media (min-width: 1024px) and (max-width: 1199.98px) {
            #productsGrid .col-xl-3 {
                width: 33.333333%;
            }
            #productsGrid .card-img-top,
            #productsGrid .bg-light {
                height: 265px !important;
            }
        }
    </style>
    <div class="card border-0 shadow-sm mb-3 bg-dark text-white">
        <div class="card-body p-4">
            <div class="d-flex justify-content-end align-items-center mb-3">
                <button id="brandShortcutsToggle" type="button" class="btn btn-sm btn-outline-light d-md-none d-inline-flex align-items-center" aria-controls="brandShortcuts" aria-expanded="false">
                    <span class="me-1">Brands</span>
                    <svg class="chevron-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8.12 9.29a1 1 0 0 1 1.41 0L12 11.76l2.47-2.47a1 1 0 1 1 1.41 1.41l-3.18 3.18a1 1 0 0 1-1.41 0L8.12 10.7a1 1 0 0 1 0-1.41z"/>
                    </svg>
                </button>
            </div>
            <form id="productSearchForm" method="GET" action="{{ route('products.index') }}" data-search-url="{{ route('products.search') }}" data-suggest-url="{{ route('products.suggest') }}">
                <div id="brandShortcuts" class="d-flex gap-2 mb-3 brand-dropdown">
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="iphone"><img src="{{ asset('assets/brand-logos/iphone.svg') }}" alt="iPhone"><span>iPhone</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="samsung"><img src="{{ asset('assets/brand-logos/samsung.svg') }}" alt="Samsung"><span>Samsung</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="infinix"><img src="{{ asset('assets/brand-logos/infinix.svg') }}" alt="Infinix"><span>Infinix</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="redmi"><img src="{{ asset('assets/brand-logos/redmi.svg') }}" alt="Redmi"><span>Redmi</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="xiaomi"><img src="{{ asset('assets/brand-logos/xiaomi.svg') }}" alt="Xiaomi"><span>Xiaomi</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="poco"><img src="{{ asset('assets/brand-logos/poco.svg') }}" alt="Poco"><span>Poco</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="huawei"><img src="{{ asset('assets/brand-logos/huawei.svg') }}" alt="Huawei"><span>Huawei</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="oppo"><img src="{{ asset('assets/brand-logos/oppo.svg') }}" alt="Oppo"><span>Oppo</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="vivo"><img src="{{ asset('assets/brand-logos/vivo.svg') }}" alt="Vivo"><span>Vivo</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="realme"><img src="{{ asset('assets/brand-logos/realme.svg') }}" alt="Realme"><span>Realme</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="tecno"><img src="{{ asset('assets/brand-logos/tecno.svg') }}" alt="Tecno"><span>Tecno</span></button>
                    <button type="button" class="brand-shortcut-btn" data-brand-keyword="itel"><img src="{{ asset('assets/brand-logos/itel.svg') }}" alt="Itel"><span>Itel</span></button>
                </div>
                <div class="row g-2" id="productsSearchFields">
                    <div class="col-lg-6">
                        <label class="form-label text-white-50">Cari cepat</label>
                        <div class="position-relative" id="productsSearchControl">
                            <div class="input-group">
                                <input id="keywordInput" type="text" name="keyword" class="form-control form-control-lg" placeholder="Nama produk, merk, kategori, etalase" value="{{ request('keyword') }}">
                                <button id="productsVoiceToggle" class="btn btn-outline-light position-relative" type="button" aria-label="Mulai rekam suara">
                                    <svg class="mic-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3Zm5-3a1 1 0 1 1 2 0 7 7 0 0 1-6 6.92V20h3a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2h3v-2.08A7 7 0 0 1 5 11a1 1 0 1 1 2 0 5 5 0 0 0 10 0Z"/>
                                    </svg>
                                </button>
                                <button id="productsFiltersToggle" class="btn btn-outline-light d-md-none" type="button" aria-label="Tampilkan filter" aria-controls="productsFiltersPanel" aria-expanded="false">
                                    <svg class="chevron-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.12 9.29a1 1 0 0 1 1.41 0L12 11.76l2.47-2.47a1 1 0 1 1 1.41 1.41l-3.18 3.18a1 1 0 0 1-1.41 0L8.12 10.7a1 1 0 0 1 0-1.41z"/>
                                    </svg>
                                </button>
                            </div>
                            <div id="productsSuggestionWrap" class="d-none position-absolute top-100 start-0 end-0 bg-white border rounded shadow-sm mt-1 overflow-auto" style="max-height: 280px; z-index: 1050;"></div>
                        </div>
                        <small id="productsVoiceError" class="text-warning d-none">Voice input tidak tersedia.</small>
                    </div>
                    <div id="productsFiltersPanel" class="col-12 col-lg-6">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label text-white-50">Kategori</label>
                                <select id="categoryFilter" name="category_id" class="form-select form-select-lg">
                                    <option value="">Semua kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white-50">Brands</label>
                                <select id="brandFilter" name="brand_id" class="form-select form-select-lg">
                                    <option value="">Semua brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span id="productsHint" class="text-white-50 small">Ketik minimal 2 kata lalu pilih dari saran.</span>
                    <span id="productsExactBadge" class="badge text-bg-light text-dark d-none"></span>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Hasil pencarian</span>
            <span class="badge text-bg-dark" id="resultCount">{{ $products->count() }} item</span>
        </div>
        <div class="card-body">
            <div id="productsGrid" class="row g-3">
                @forelse($products as $product)
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="card h-100 border">
                            @if($product->category->image_path)
                                <img src="{{ asset('storage/'.$product->category->image_path) }}" alt="{{ $product->category->name }}" class="card-img-top bg-light" style="height: 280px; object-fit: contain;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 280px;">Tanpa Gambar</div>
                            @endif
                            <div class="card-body">
                                <h2 class="h6 mb-2">{{ $product->name }}</h2>
                                <p class="mb-1 text-secondary small">{{ $product->category->name }}</p>
                                <p class="mb-2 text-secondary small">Bentuk Kamera: {{ $product->phoneType->camera_shape ?? '-' }}</p>
                                <p class="mb-0"><span class="badge text-bg-success">Posisi Etalase: {{ $product->resolved_showcase }}</span></p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-secondary">Data produk tidak ditemukan.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="mt-3" id="productsPagination">
        {{ $products->links() }}
    </div>

    <script>
        (() => {
            const form = document.getElementById('productSearchForm');
            const searchUrl = form.dataset.searchUrl;
            const suggestUrl = form.dataset.suggestUrl;
            const keywordInput = document.getElementById('keywordInput');
            const searchControl = document.getElementById('productsSearchControl');
            const voiceToggle = document.getElementById('productsVoiceToggle');
            const voiceError = document.getElementById('productsVoiceError');
            const suggestionWrap = document.getElementById('productsSuggestionWrap');
            const hintText = document.getElementById('productsHint');
            const exactBadge = document.getElementById('productsExactBadge');
            const categoryFilter = document.getElementById('categoryFilter');
            const brandFilter = document.getElementById('brandFilter');
            const productsGrid = document.getElementById('productsGrid');
            const resultCount = document.getElementById('resultCount');
            const pagination = document.getElementById('productsPagination');
            const searchFields = document.getElementById('productsSearchFields');
            const brandShortcuts = document.getElementById('brandShortcuts');
            const brandShortcutsToggle = document.getElementById('brandShortcutsToggle');
            const brandShortcutButtons = Array.from(document.querySelectorAll('.brand-shortcut-btn'));
            const filtersPanel = document.getElementById('productsFiltersPanel');
            const filtersToggle = document.getElementById('productsFiltersToggle');
            let debounceTimer;
            let recognition = null;
            let isRecording = false;
            let isBrandShortcutsVisible = true;
            const mobileMediaQuery = window.matchMedia('(max-width: 767.98px)');
            let isFiltersVisible = false;
            let exactMode = false;
            let activeSuggestionIndex = -1;
            let suggestionNames = [];

            const renderCards = (items) => {
                if (!items.length) {
                    productsGrid.innerHTML = '<div class="col-12 text-center py-4 text-secondary">Data produk tidak ditemukan.</div>';
                    return;
                }

                productsGrid.innerHTML = items.map((item) => `
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="card h-100 border">
                            ${item.category_image
                                ? `<img src="${item.category_image}" alt="${item.category}" class="card-img-top bg-light" style="height: 280px; object-fit: contain;">`
                                : '<div class="bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 280px;">Tanpa Gambar</div>'}
                            <div class="card-body">
                                <h2 class="h6 mb-2">${item.name}</h2>
                                <p class="mb-1 text-secondary small">${item.category}</p>
                                <p class="mb-2 text-secondary small">Bentuk Kamera: ${item.camera_shape ?? '-'}</p>
                                <p class="mb-0"><span class="badge text-bg-success">Posisi Etalase: ${item.showcase}</span></p>
                            </div>
                        </div>
                    </div>
                `).join('');
            };

            const loadRealtime = async () => {
                const params = new URLSearchParams({
                    keyword: keywordInput.value.trim(),
                    category_id: categoryFilter.value,
                    brand_id: brandFilter.value,
                    exact: exactMode ? '1' : '0',
                });

                const response = await fetch(`${searchUrl}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const payload = await response.json();
                renderCards(payload.items);
                resultCount.textContent = `${payload.count} item`;
                pagination.classList.add('d-none');
            };

            const canSearchKeyword = (keyword) => /^\S+\s+\S/.test(keyword);

            const showHint = (state) => {
                hintText.classList.toggle('d-none', !state);
            };

            const showExactBadge = (productName) => {
                const hasValue = !!productName;
                exactBadge.classList.toggle('d-none', !hasValue);
                exactBadge.textContent = hasValue ? `Produk: ${productName}` : '';
            };

            const clearSuggestions = () => {
                suggestionWrap.classList.add('d-none');
                suggestionWrap.innerHTML = '';
                activeSuggestionIndex = -1;
                suggestionNames = [];
            };

            const highlightMatch = (text, keyword) => {
                const safeText = String(text ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
                const rawKeyword = String(keyword ?? '').trim();
                if (!rawKeyword) {
                    return safeText;
                }
                const escapedKeyword = rawKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                return safeText.replace(new RegExp(`(${escapedKeyword})`, 'ig'), '<mark class="bg-warning-subtle px-0">$1</mark>');
            };

            const renderSuggestions = (items) => {
                if (!items.length) {
                    clearSuggestions();
                    return;
                }
                suggestionNames = items;
                activeSuggestionIndex = -1;
                suggestionWrap.innerHTML = items.map((name, index) => `
                    <button type="button" class="w-100 text-start border-0 bg-white px-3 py-2 border-bottom suggestion-item" data-index="${index}" role="option">
                        ${highlightMatch(name, keywordInput.value)}
                    </button>
                `).join('');
                suggestionWrap.classList.remove('d-none');
            };

            const updateActiveSuggestion = () => {
                const buttons = Array.from(suggestionWrap.querySelectorAll('.suggestion-item'));
                buttons.forEach((button, index) => {
                    const active = index === activeSuggestionIndex;
                    button.classList.toggle('bg-light', active);
                    if (active) {
                        button.scrollIntoView({ block: 'nearest' });
                    }
                });
            };

            const applySuggestion = (name) => {
                keywordInput.value = name;
                exactMode = true;
                showExactBadge(name);
                clearSuggestions();
                showHint(false);
                loadRealtime();
            };

            const loadSuggestions = async () => {
                const keyword = keywordInput.value.trim();
                if (!canSearchKeyword(keyword)) {
                    clearSuggestions();
                    return;
                }

                const params = new URLSearchParams({
                    keyword,
                    category_id: categoryFilter.value,
                    brand_id: brandFilter.value,
                });
                const response = await fetch(`${suggestUrl}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                renderSuggestions(payload.items || []);
                const exactMatch = (payload.items || []).some((item) => String(item).toLowerCase() === keyword.toLowerCase());
                if ((payload.items || []).length > 1 && !exactMatch) {
                    productsGrid.innerHTML = '<div class="col-12 text-center py-4 text-secondary">Pilih salah satu nama produk dari daftar saran.</div>';
                    resultCount.textContent = '0 item';
                }
            };

            const updateVoiceState = () => {
                if (isRecording) {
                    voiceToggle.classList.add('is-recording');
                    voiceToggle.setAttribute('aria-label', 'Hentikan rekam suara');
                } else {
                    voiceToggle.classList.remove('is-recording');
                    voiceToggle.setAttribute('aria-label', 'Mulai rekam suara');
                }
            };

            const formatVoiceText = (rawText) => {
                const text = String(rawText ?? '').trim().replace(/\s+/g, ' ');
                return text.length ? text.charAt(0).toLowerCase() + text.slice(1) : '';
            };

            const setupSpeechRecognition = () => {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRecognition) {
                    voiceToggle.disabled = true;
                    voiceError.classList.remove('d-none');
                    return;
                }

                recognition = new SpeechRecognition();
                recognition.lang = 'id-ID';
                recognition.interimResults = true;
                recognition.maxAlternatives = 1;
                recognition.continuous = false;

                recognition.addEventListener('start', () => {
                    isRecording = true;
                    updateVoiceState();
                    voiceError.classList.add('d-none');
                });

                recognition.addEventListener('result', (event) => {
                    let transcript = '';
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        transcript += event.results[i][0].transcript;
                    }
                    keywordInput.value = formatVoiceText(transcript);
                    exactMode = false;
                    showExactBadge(null);
                    triggerSearch();
                });

                recognition.addEventListener('error', (event) => {
                    isRecording = false;
                    updateVoiceState();
                    if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                        voiceError.textContent = 'Izin mikrofon ditolak.';
                        voiceError.classList.remove('d-none');
                    } else if (event.error === 'audio-capture') {
                        voiceError.textContent = 'Mikrofon tidak tersedia.';
                        voiceError.classList.remove('d-none');
                    }
                });

                recognition.addEventListener('end', () => {
                    isRecording = false;
                    updateVoiceState();
                });
            };

            const triggerSearch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(async () => {
                    const keyword = keywordInput.value.trim();
                    if (keyword === '') {
                        clearSuggestions();
                        showHint(true);
                        showExactBadge(null);
                        exactMode = false;
                        await loadRealtime();
                        return;
                    }

                    if (!exactMode && !canSearchKeyword(keyword)) {
                        clearSuggestions();
                        showHint(true);
                        productsGrid.innerHTML = '<div class="col-12 text-center py-4 text-secondary">Ketik minimal 2 kata lalu pilih dari saran.</div>';
                        resultCount.textContent = '0 item';
                        return;
                    }

                    if (!exactMode) {
                        showHint(true);
                        showExactBadge(null);
                        try {
                            await loadSuggestions();
                        } catch (error) {
                            voiceError.textContent = 'Gagal memuat saran produk.';
                            voiceError.classList.remove('d-none');
                        }
                        return;
                    }

                    showHint(false);
                    await loadRealtime();
                }, 300);
            };

            const syncBrandShortcutsVisibility = () => {
                if (!mobileMediaQuery.matches) {
                    brandShortcuts.classList.remove('is-collapsed');
                    brandShortcutsToggle.setAttribute('aria-expanded', 'true');
                    return;
                }
                brandShortcuts.classList.toggle('is-collapsed', !isBrandShortcutsVisible);
                brandShortcutsToggle.setAttribute('aria-expanded', isBrandShortcutsVisible ? 'true' : 'false');
            };

            const syncFiltersVisibility = () => {
                if (!mobileMediaQuery.matches) {
                    filtersPanel.classList.remove('is-collapsed');
                    filtersToggle?.setAttribute('aria-expanded', 'true');
                    return;
                }
                filtersPanel.classList.toggle('is-collapsed', !isFiltersVisible);
                filtersToggle?.setAttribute('aria-expanded', isFiltersVisible ? 'true' : 'false');
            };

            const syncBrandShortcutState = () => {
                const keyword = keywordInput.value.trim().toLowerCase();
                brandShortcutButtons.forEach((button) => {
                    const brand = (button.dataset.brandKeyword || '').toLowerCase();
                    const isActive = keyword.startsWith(brand) && keyword.length > 0;
                    button.classList.toggle('is-active', isActive);
                });
            };

            const syncBrandDropdownByKeyword = (keyword) => {
                const normalized = (keyword || '').trim().toLowerCase();
                const options = Array.from(brandFilter.options);
                const matched = options.find((option) => {
                    if (!option.value) {
                        return false;
                    }
                    const optionText = (option.textContent || '').trim().toLowerCase();
                    return optionText === normalized || optionText.includes(normalized) || normalized.includes(optionText);
                });
                brandFilter.value = matched ? matched.value : '';
            };

            keywordInput.addEventListener('input', () => {
                exactMode = false;
                showExactBadge(null);
                triggerSearch();
            });
            keywordInput.addEventListener('input', syncBrandShortcutState);
            keywordInput.addEventListener('focus', () => searchControl.classList.add('is-focused'));
            keywordInput.addEventListener('blur', () => searchControl.classList.remove('is-focused'));
            keywordInput.addEventListener('keydown', (event) => {
                if (suggestionWrap.classList.contains('d-none')) {
                    return;
                }
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    activeSuggestionIndex = Math.min(activeSuggestionIndex + 1, suggestionNames.length - 1);
                    updateActiveSuggestion();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    activeSuggestionIndex = Math.max(activeSuggestionIndex - 1, 0);
                    updateActiveSuggestion();
                } else if (event.key === 'Enter') {
                    if (activeSuggestionIndex >= 0 && suggestionNames[activeSuggestionIndex]) {
                        event.preventDefault();
                        applySuggestion(suggestionNames[activeSuggestionIndex]);
                    }
                } else if (event.key === 'Escape') {
                    clearSuggestions();
                }
            });
            categoryFilter.addEventListener('change', () => {
                exactMode = false;
                showExactBadge(null);
                triggerSearch();
            });
            brandFilter.addEventListener('change', () => {
                exactMode = false;
                showExactBadge(null);
                triggerSearch();
            });
            brandShortcutsToggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                isBrandShortcutsVisible = !isBrandShortcutsVisible;
                syncBrandShortcutsVisibility();
            });
            filtersToggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                isFiltersVisible = !isFiltersVisible;
                syncFiltersVisibility();
            });
            document.addEventListener('click', (event) => {
                if (!mobileMediaQuery.matches) {
                    return;
                }
                const target = event.target;
                const clickedInsidePanel = brandShortcuts.contains(target);
                const clickedToggle = brandShortcutsToggle.contains(target);
                if (!clickedInsidePanel && !clickedToggle && isBrandShortcutsVisible) {
                    isBrandShortcutsVisible = false;
                    syncBrandShortcutsVisibility();
                }
                const clickedInsideFilters = filtersPanel.contains(target);
                const clickedFiltersToggle = filtersToggle.contains(target);
                if (!clickedInsideFilters && !clickedFiltersToggle && isFiltersVisible) {
                    isFiltersVisible = false;
                    syncFiltersVisibility();
                }
            });
            brandShortcutButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const brandKeyword = button.dataset.brandKeyword || '';
                    keywordInput.value = `${brandKeyword} `;
                    syncBrandDropdownByKeyword(brandKeyword);
                    keywordInput.focus();
                    syncBrandShortcutState();
                    exactMode = false;
                    showExactBadge(null);
                    if (mobileMediaQuery.matches) {
                        isBrandShortcutsVisible = false;
                    }
                    syncBrandShortcutsVisibility();
                    triggerSearch();
                });
            });
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                if (exactMode) {
                    loadRealtime();
                }
            });
            suggestionWrap.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-index]');
                if (!button) {
                    return;
                }
                const index = Number(button.dataset.index);
                if (!Number.isNaN(index) && suggestionNames[index]) {
                    applySuggestion(suggestionNames[index]);
                }
            });
            document.addEventListener('click', (event) => {
                if (!event.target.closest('#productsSearchControl')) {
                    clearSuggestions();
                }
            });
            voiceToggle.addEventListener('click', () => {
                if (!recognition) {
                    voiceError.classList.remove('d-none');
                    return;
                }
                try {
                    if (isRecording) {
                        recognition.stop();
                    } else {
                        recognition.start();
                    }
                } catch (error) {
                    voiceError.textContent = 'Voice input tidak dapat dijalankan saat ini.';
                    voiceError.classList.remove('d-none');
                }
            });
            setupSpeechRecognition();
            updateVoiceState();
            syncBrandShortcutState();
            searchFields.classList.remove('d-none');
            mobileMediaQuery.addEventListener('change', syncBrandShortcutsVisibility);
            syncBrandShortcutsVisibility();
            syncFiltersVisibility();
            showHint(true);
        })();
    </script>
@endsection
