@extends('layouts.app')

@section('content')
    <style>
        #publicSearchControl {
            transition: box-shadow .2s ease, border-color .2s ease;
            border-radius: .5rem;
        }
        #publicSearchControl.is-focused {
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
        }
        #publicVoiceToggle {
            min-width: 46px;
            width: 46px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .15s ease, background-color .2s ease, border-color .2s ease, box-shadow .2s ease, color .2s ease;
            will-change: transform;
        }
        #publicVoiceToggle .mic-icon {
            width: 20px;
            height: 20px;
            display: block;
            transition: transform .2s ease, fill .2s ease;
            fill: currentColor;
        }
        #publicVoiceToggle:hover {
            background: rgba(13, 110, 253, .08);
            border-color: rgba(13, 110, 253, .35);
            color: #0d6efd;
            transform: translateY(-1px);
        }
        #publicVoiceToggle:active {
            transform: scale(.96);
            background: rgba(13, 110, 253, .14);
        }
        #publicVoiceToggle:focus-visible {
            outline: none;
            box-shadow: 0 0 0 .18rem rgba(13, 110, 253, .2);
        }
        #publicVoiceToggle.is-recording {
            color: #fff;
            background: #dc3545;
            border-color: #dc3545;
            animation: micPulse 1.1s ease-in-out infinite;
        }
        #publicVoiceToggle.is-recording .mic-icon {
            transform: scale(1.08);
        }
        #publicVoiceToggle.is-recording::before {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 10px;
            border: 2px solid rgba(220, 53, 69, .45);
            animation: micRing 1.1s ease-out infinite;
        }
        @keyframes micPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }
        @keyframes micRing {
            0% { transform: scale(.85); opacity: .9; }
            100% { transform: scale(1.25); opacity: 0; }
        }
        #publicBrandShortcuts {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: .25rem;
        }
        #publicBrandShortcutsToggle {
            border-color: rgba(0,0,0,.35);
            color: #212529;
            font-weight: 600;
            letter-spacing: .01em;
            border-width: 2px;
            box-shadow: 0 .25rem .75rem rgba(0,0,0,.12);
            transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease, color .18s ease;
        }
        #publicBrandShortcutsToggle:hover {
            background: rgba(13, 110, 253, .08);
            border-color: rgba(13, 110, 253, .45);
            color: #0d6efd;
        }
        #publicBrandShortcutsToggle[aria-expanded="true"] {
            background: rgba(13, 110, 253, .12);
            border-color: rgba(13, 110, 253, .5);
            color: #0d6efd;
        }
        #publicBrandShortcutsToggle .chevron-icon {
            width: 14px;
            height: 14px;
            transition: transform .18s ease;
            transform-origin: center;
        }
        #publicBrandShortcutsToggle[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
        }
        #publicSearchControl .input-group > .btn {
            padding-left: .6rem;
            padding-right: .6rem;
        }
        #publicSearchControl .form-control {
            min-height: 46px;
        }
        #publicProductsGrid .card {
            overflow: hidden;
            border-radius: .9rem;
        }
        #publicProductsGrid .card-body {
            padding: .82rem .88rem;
        }
        #publicProductsGrid h2 {
            font-size: .96rem;
            line-height: 1.32;
            min-height: 2.62em;
        }
        #publicProductsGrid p {
            line-height: 1.25;
        }
        #publicSuggestionWrap {
            border-radius: .75rem !important;
        }
        #publicSuggestionWrap .suggestion-item {
            min-height: 44px;
            font-size: .95rem;
        }
        #publicSuggestionWrap .suggestion-item mark {
            border-radius: .2rem;
        }
        .public-brand-shortcut-btn {
            border: 1px solid rgba(0,0,0,.15);
            background: #fff;
            color: #212529;
            border-radius: .75rem;
            padding: .35rem .55rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            transition: all .15s ease;
        }
        .public-brand-shortcut-btn:hover {
            background: #f8f9fa;
            border-color: rgba(0,0,0,.3);
            transform: translateY(-1px);
        }
        .public-brand-shortcut-btn.is-active {
            background: rgba(13, 110, 253, .12);
            border-color: rgba(13, 110, 253, .4);
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
        }
        .public-brand-shortcut-btn img {
            width: 22px;
            height: 22px;
            object-fit: contain;
            background: #fff;
            border-radius: .35rem;
            padding: 2px;
        }
        @media (max-width: 767.98px) {
            #publicBrandShortcuts.brand-dropdown {
                background: rgba(0,0,0,.35);
                backdrop-filter: blur(3px);
                border: 1px solid rgba(255,255,255,.18);
                border-radius: .75rem;
                padding: .6rem;
                box-shadow: 0 .5rem 1rem rgba(0,0,0,.25);
                transition: opacity .22s ease, transform .22s ease, max-height .28s ease, margin .22s ease, padding .22s ease, border-color .22s ease, box-shadow .22s ease;
            }
            #publicBrandShortcuts {
                overflow-x: visible;
                white-space: normal;
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .75rem;
                transform-origin: top center;
                max-height: 1400px;
                opacity: 1;
                transform: translateY(0);
                overflow: hidden;
            }
            #publicBrandShortcuts.is-collapsed {
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
            .public-brand-shortcut-btn {
                width: 100%;
                min-height: 96px;
                flex-direction: column;
                justify-content: center;
                text-align: center;
                padding: .7rem .45rem;
                gap: .4rem;
                border: 1px solid rgba(255,255,255,.2);
                background: rgba(255,255,255,.08);
                color: #fff;
            }
            .public-brand-shortcut-btn:hover {
                background: rgba(255,255,255,.18);
                border-color: rgba(255,255,255,.35);
            }
            .public-brand-shortcut-btn.is-active {
                background: rgba(255,255,255,.26);
                border-color: rgba(255,255,255,.52);
                box-shadow: 0 0 0 .2rem rgba(255,255,255,.14);
            }
            .public-brand-shortcut-btn span {
                font-size: .78rem;
                line-height: 1.1;
            }
            .public-brand-shortcut-btn img {
                width: 38px;
                height: 38px;
            }
            #publicProductsGrid .card-img-top,
            #publicProductsGrid .bg-light {
                height: 200px !important;
            }
            #publicProductsGrid .col-6 {
                width: 50%;
            }
        }
        @media (max-width: 575.98px) {
            .card-body.p-3.p-md-4 {
                padding: .85rem !important;
            }
            #publicBrandShortcuts {
                gap: .6rem !important;
            }
            #publicProductsGrid .card-body {
                padding: .76rem .82rem;
            }
            #publicProductsGrid h2 {
                font-size: .93rem;
            }
            #publicProductsGrid .col-6 {
                width: 100%;
            }
            #publicProductsGrid .card-img-top,
            #publicProductsGrid .bg-light {
                height: 180px !important;
            }
            #publicSuggestionWrap {
                max-height: 220px !important;
            }
            #publicHint {
                font-size: .78rem !important;
            }
            #publicExactBadge {
                font-size: .72rem;
            }
        }
        @media (min-width: 375px) and (max-width: 413.98px) {
            #publicProductsGrid .card-img-top,
            #publicProductsGrid .bg-light {
                height: 190px !important;
            }
            #publicHint {
                font-size: .8rem !important;
            }
        }
        @media (min-width: 414px) and (max-width: 575.98px) {
            #publicProductsGrid .card-img-top,
            #publicProductsGrid .bg-light {
                height: 205px !important;
            }
        }
        @media (max-width: 374.98px) {
            #publicBrandShortcuts {
                gap: .5rem !important;
            }
            .public-brand-shortcut-btn {
                min-height: 86px;
                padding: .55rem .35rem;
            }
            .public-brand-shortcut-btn img {
                width: 34px;
                height: 34px;
            }
            .public-brand-shortcut-btn span {
                font-size: .72rem;
            }
            #publicVoiceToggle {
                width: 44px;
                min-width: 44px;
                height: 44px;
            }
            #publicKeyword {
                font-size: .93rem;
            }
            #publicSuggestionWrap .suggestion-item {
                font-size: .9rem;
            }
            #publicProductsGrid .card-body {
                padding: .72rem .72rem;
            }
            #publicProductsGrid h2 {
                font-size: .9rem;
            }
        }
        @media (min-width: 768px) and (max-width: 1023.98px) {
            #publicProductsGrid .col-md-4 {
                width: 50%;
            }
            #publicProductsGrid .card-img-top,
            #publicProductsGrid .bg-light {
                height: 230px !important;
            }
        }
        @media (min-width: 1024px) and (max-width: 1199.98px) {
            #publicProductsGrid .col-lg-3 {
                width: 33.333333%;
            }
            #publicProductsGrid .card-img-top,
            #publicProductsGrid .bg-light {
                height: 245px !important;
            }
        }
    </style>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3 p-md-4">
            <div class="row g-2 g-md-3 align-items-start" id="publicSearchForm" data-search-url="{{ route('public.products.search') }}" data-suggest-url="{{ route('public.products.suggest') }}">
                <div class="col-12">
                    <div class="d-flex justify-content-end align-items-center mb-2">
                        <button id="publicBrandShortcutsToggle" type="button" class="btn btn-sm btn-outline-secondary d-md-none d-inline-flex align-items-center" aria-controls="publicBrandShortcuts" aria-expanded="false">
                            <span class="me-1">Brands</span>
                            <svg class="chevron-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.12 9.29a1 1 0 0 1 1.41 0L12 11.76l2.47-2.47a1 1 0 1 1 1.41 1.41l-3.18 3.18a1 1 0 0 1-1.41 0L8.12 10.7a1 1 0 0 1 0-1.41z"/>
                            </svg>
                        </button>
                    </div>
                    <div id="publicBrandShortcuts" class="d-flex gap-2 brand-dropdown">
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="iphone"><img src="{{ asset('assets/brand-logos/iphone.svg') }}" alt="iPhone"><span>iPhone</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="samsung"><img src="{{ asset('assets/brand-logos/samsung.svg') }}" alt="Samsung"><span>Samsung</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="realme"><img src="{{ asset('assets/brand-logos/realme.svg') }}" alt="Realme"><span>Realme</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="redmi"><img src="{{ asset('assets/brand-logos/redmi.svg') }}" alt="Redmi"><span>Redmi</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="poco"><img src="{{ asset('assets/brand-logos/poco.svg') }}" alt="Poco"><span>Poco</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="xiaomi"><img src="{{ asset('assets/brand-logos/xiaomi.svg') }}" alt="Xiaomi"><span>Xiaomi</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="oppo"><img src="{{ asset('assets/brand-logos/oppo.svg') }}" alt="Oppo"><span>Oppo</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="vivo"><img src="{{ asset('assets/brand-logos/vivo.svg') }}" alt="Vivo"><span>Vivo</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="infinix"><img src="{{ asset('assets/brand-logos/infinix.svg') }}" alt="Infinix"><span>Infinix</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="tecno"><img src="{{ asset('assets/brand-logos/tecno.svg') }}" alt="Tecno"><span>Tecno</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="itel"><img src="{{ asset('assets/brand-logos/itel.svg') }}" alt="Itel"><span>Itel</span></button>
                        <button type="button" class="public-brand-shortcut-btn" data-brand-keyword="huawei"><img src="{{ asset('assets/brand-logos/huawei.svg') }}" alt="Huawei"><span>Huawei</span></button>
                    </div>
                </div>
                <div class="col-12">
                    <div class="position-relative" id="publicSearchControl">
                        <div class="input-group">
                            <input id="publicKeyword" type="text" class="form-control" placeholder="Cari nama produk (ketik 2 kata, lalu pilih dari saran)" value="{{ $initialKeyword }}" autocomplete="off">
                            <button id="publicVoiceToggle" class="btn btn-outline-secondary position-relative" type="button" aria-label="Mulai rekam suara">
                                <svg class="mic-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3Zm5-3a1 1 0 1 1 2 0 7 7 0 0 1-6 6.92V20h3a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2h3v-2.08A7 7 0 0 1 5 11a1 1 0 1 1 2 0 5 5 0 0 0 10 0Z"/>
                                </svg>
                            </button>
                        </div>
                        <div id="publicSuggestionWrap" class="d-none position-absolute top-100 start-0 end-0 bg-white border rounded shadow-sm mt-1 overflow-auto" style="max-height: 280px; z-index: 1050;"></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <label class="uiverse-switch">
                            <input class="visibility-switch" type="checkbox" id="publicSizeModeSwitch" role="switch">
                            <span class="uiverse-slider"></span>
                        </label>
                        <label class="form-label mb-0" for="publicSizeModeSwitch">Cari etalase berdasarkan ukuran antigores</label>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mt-3">
                <span id="publicLoading" class="badge text-bg-secondary d-none">Memuat...</span>
                <span id="publicError" class="text-danger small d-none">Koneksi bermasalah. Coba lagi.</span>
                <span id="publicHint" class="text-secondary small">Mode produk aktif. Ketik minimal 2 kata lalu pilih dari saran.</span>
                <span id="publicExactBadge" class="badge text-bg-primary d-none"></span>
            </div>
        </div>
    </div>

    <div class="row g-3" id="publicProductsGrid">
        @forelse($initialItems as $item)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 border">
                    @if(!empty($item['image_url']))
                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="card-img-top bg-light" style="height: 260px; object-fit: contain;" loading="lazy">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 260px;">Tanpa Gambar</div>
                    @endif
                    <div class="card-body p-3">
                        <h2 class="h6 mb-2">{{ $item['name'] }}</h2>
                        <p class="mb-1 small text-secondary">{{ $item['category'] }}</p>
                        <p class="mb-1 small text-secondary">Detail Etalase: {{ $item['showcase'] ?? '-' }}</p>
                        <p class="mb-1 small text-secondary">Ukuran Antigores: {{ $item['antigores_size'] ?? '-' }}</p>
                        <p class="mb-2 small text-secondary">Bentuk Kamera: {{ $item['camera_shape'] ?? '-' }}</p>
                        <span class="badge text-bg-success">Etalase Aktif</span>
                    </div>
                </div>
            </div>
        @empty
            @if($initialKeyword !== '')
                <div class="col-12 text-center text-secondary py-4">Produk dengan kata kunci "{{ $initialKeyword }}" tidak ditemukan.</div>
            @else
                <div class="col-12 text-center text-secondary py-4">Produk akan ditampilkan setelah kata kedua diketik dan pilihan produk dipilih.</div>
            @endif
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-3">
        <button id="publicLoadMoreBtn" class="btn btn-outline-dark btn-sm d-none">Muat Lebih Banyak</button>
    </div>

    <div id="publicSentinel" class="py-2"></div>

    <script>
        (() => {
            const form = document.getElementById('publicSearchForm');
            const searchUrl = form.dataset.searchUrl;
            const suggestUrl = form.dataset.suggestUrl;
            const keywordInput = document.getElementById('publicKeyword');
            const searchControl = document.getElementById('publicSearchControl');
            const sizeModeSwitch = document.getElementById('publicSizeModeSwitch');
            const voiceToggle = document.getElementById('publicVoiceToggle');
            const suggestionWrap = document.getElementById('publicSuggestionWrap');
            const brandShortcutsWrap = document.getElementById('publicBrandShortcuts');
            const brandShortcutsToggle = document.getElementById('publicBrandShortcutsToggle');
            const brandShortcutButtons = Array.from(document.querySelectorAll('.public-brand-shortcut-btn'));
            const grid = document.getElementById('publicProductsGrid');
            const loadingBadge = document.getElementById('publicLoading');
            const errorText = document.getElementById('publicError');
            const hintText = document.getElementById('publicHint');
            const exactBadge = document.getElementById('publicExactBadge');
            const sentinel = document.getElementById('publicSentinel');
            const loadMoreButton = document.getElementById('publicLoadMoreBtn');
            let debounceTimer;
            let currentPage = {{ $initialPagination['current_page'] ?? 1 }};
            let hasMore = {{ ($initialPagination['has_more'] ?? false) ? 'true' : 'false' }};
            let currentAbortController = null;
            let isLoading = false;
            let exactMode = false;
            let activeSuggestionIndex = -1;
            let suggestionNames = [];
            let recognition = null;
            let isRecording = false;
            const voiceLanguage = 'id-ID';
            const mobileMediaQuery = window.matchMedia('(max-width: 767.98px)');
            let isBrandShortcutsVisible = true;

            const escapeHtml = (text) => String(text ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const renderCard = (item) => `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 border">
                        ${item.image_url
                            ? `<img src="${item.image_url}" alt="${escapeHtml(item.name)}" class="card-img-top bg-light" style="height: 260px; object-fit: contain;" loading="lazy">`
                            : '<div class="bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 260px;">Tanpa Gambar</div>'}
                        <div class="card-body p-3">
                            <h2 class="h6 mb-2">${escapeHtml(item.name)}</h2>
                            <p class="mb-1 small text-secondary">${escapeHtml(item.category)}</p>
                            <p class="mb-1 small text-secondary">Detail Etalase: ${escapeHtml(item.showcase || '-')}</p>
                            <p class="mb-1 small text-secondary">Ukuran Antigores: ${escapeHtml(item.antigores_size || '-')}</p>
                            <p class="mb-2 small text-secondary">Bentuk Kamera: ${escapeHtml(item.camera_shape || '-')}</p>
                            <span class="badge text-bg-success">Etalase Aktif</span>
                        </div>
                    </div>
                </div>
            `;

            const renderShowcases = (items) => {
                if (!items.length) {
                    const sizeKeyword = keywordInput.value.trim();
                    grid.innerHTML = sizeKeyword === ''
                        ? '<div class="col-12 text-center text-secondary py-4">Masukkan ukuran antigores untuk melihat data etalase.</div>'
                        : '<div class="col-12 text-center text-secondary py-4">Data etalase tidak ditemukan.</div>';
                    return;
                }

                grid.innerHTML = items.map((item) => `
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 border">
                            <div class="card-body p-3">
                                <h2 class="h6 mb-2">${escapeHtml(item.name || '-')}</h2>
                                <p class="mb-1 small text-secondary">Ukuran: ${escapeHtml(item.antigores_size || '-')}</p>
                                <p class="mb-0 small text-secondary">Kamera: ${escapeHtml(item.camera_shape || '-')}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            };

            const updateLoadMoreVisibility = () => {
                if ('IntersectionObserver' in window) {
                    loadMoreButton.classList.add('d-none');
                    return;
                }
                loadMoreButton.classList.toggle('d-none', !hasMore);
            };

            const setLoading = (state) => {
                isLoading = state;
                loadingBadge.classList.toggle('d-none', !state);
            };

            const showError = (state, message = 'Koneksi bermasalah. Coba lagi.') => {
                errorText.classList.toggle('d-none', !state);
                if (state) {
                    errorText.textContent = message;
                } else {
                    errorText.textContent = 'Koneksi bermasalah. Coba lagi.';
                }
            };

            const showHint = (state) => {
                if (!hintText) {
                    return;
                }
                hintText.classList.toggle('d-none', !state);
            };

            const showExactBadge = (productName) => {
                const hasValue = !!productName;
                exactBadge.classList.toggle('d-none', !hasValue);
                if (hasValue) {
                    exactBadge.textContent = `Produk: ${productName}`;
                } else {
                    exactBadge.textContent = '';
                }
            };

            const clearSuggestions = () => {
                suggestionWrap.classList.add('d-none');
                suggestionWrap.innerHTML = '';
                activeSuggestionIndex = -1;
                suggestionNames = [];
            };

            const canSearchKeyword = (keyword) => {
                return /^\S+\s+\S/.test(keyword);
            };

            const canSearchBySize = (sizeKeyword) => {
                return String(sizeKeyword ?? '').trim().length >= 2;
            };

            const isSizeMode = () => sizeModeSwitch.checked;

            const buildNoResultMessage = () => {
                const keyword = keywordInput.value.trim();
                if (keyword) {
                    return `Produk dengan kata kunci "<strong>${escapeHtml(keyword)}</strong>" tidak ditemukan.`;
                }
                return 'Data produk tidak ditemukan.';
            };

            const syncBrandShortcutState = () => {
                const keyword = keywordInput.value.trim().toLowerCase();
                brandShortcutButtons.forEach((button) => {
                    const brand = (button.dataset.brandKeyword || '').toLowerCase();
                    const isActive = keyword.startsWith(brand) && keyword.length > 0;
                    button.classList.toggle('is-active', isActive);
                });
            };

            const syncBrandShortcutsVisibility = () => {
                if (!brandShortcutsToggle) {
                    return;
                }
                if (!mobileMediaQuery.matches) {
                    brandShortcutsWrap.classList.remove('is-collapsed');
                    brandShortcutsToggle.setAttribute('aria-expanded', 'true');
                    return;
                }
                brandShortcutsWrap.classList.toggle('is-collapsed', !isBrandShortcutsVisible);
                brandShortcutsToggle.setAttribute('aria-expanded', isBrandShortcutsVisible ? 'true' : 'false');
            };

            const updateVoiceStatus = () => {
                if (isRecording) {
                    voiceToggle.classList.remove('btn-outline-secondary');
                    voiceToggle.classList.add('btn-danger');
                    voiceToggle.classList.add('is-recording');
                    voiceToggle.setAttribute('aria-label', 'Hentikan rekam suara');
                } else {
                    voiceToggle.classList.add('btn-outline-secondary');
                    voiceToggle.classList.remove('btn-danger');
                    voiceToggle.classList.remove('is-recording');
                    voiceToggle.setAttribute('aria-label', 'Mulai rekam suara');
                }
            };

            const updateModeUI = () => {
                if (isSizeMode()) {
                    keywordInput.placeholder = 'Cari ukuran antigores untuk menampilkan data etalase (contoh: 161 x 71)';
                    hintText.textContent = 'Mode etalase aktif. Ketik minimal 2 karakter ukuran antigores.';
                    brandShortcutsWrap.classList.add('d-none');
                    brandShortcutsToggle?.classList.add('d-none');
                } else {
                    keywordInput.placeholder = 'Cari nama produk (ketik 2 kata, lalu pilih dari saran)';
                    hintText.textContent = 'Mode produk aktif. Ketik minimal 2 kata lalu pilih dari saran.';
                    brandShortcutsWrap.classList.remove('d-none');
                    brandShortcutsToggle?.classList.remove('d-none');
                    syncBrandShortcutsVisibility();
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
                    return;
                }

                recognition = new SpeechRecognition();
                recognition.lang = voiceLanguage;
                recognition.interimResults = true;
                recognition.maxAlternatives = 1;
                recognition.continuous = false;

                recognition.addEventListener('start', () => {
                    isRecording = true;
                    updateVoiceStatus();
                    showError(false);
                });

                recognition.addEventListener('result', (event) => {
                    let transcript = '';
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        transcript += event.results[i][0].transcript;
                    }
                    keywordInput.value = formatVoiceText(transcript);
                    exactMode = false;
                    showExactBadge(null);
                    syncBrandShortcutState();
                    triggerSearch();
                });

                recognition.addEventListener('error', (event) => {
                    isRecording = false;
                    updateVoiceStatus();
                    if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                        showError(true, 'Akses mikrofon ditolak. Izinkan akses mikrofon untuk menggunakan voice input.');
                    } else if (event.error === 'audio-capture') {
                        showError(true, 'Mikrofon tidak tersedia pada perangkat ini.');
                    } else if (event.error !== 'no-speech') {
                        showError(true, 'Terjadi kendala pada voice input. Silakan coba lagi.');
                    }
                });

                recognition.addEventListener('end', () => {
                    isRecording = false;
                    updateVoiceStatus();
                });
            };

            const renderGridMessage = (message) => {
                grid.innerHTML = `<div class="col-12 text-center text-secondary py-4">${message}</div>`;
            };

            const highlightMatch = (text, keyword) => {
                const safeText = escapeHtml(text);
                const rawKeyword = String(keyword ?? '').trim();
                if (!rawKeyword) {
                    return safeText;
                }
                const escapedKeyword = rawKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${escapedKeyword})`, 'ig');
                return safeText.replace(regex, '<mark class="bg-warning-subtle px-0">$1</mark>');
            };

            const renderSuggestions = (items) => {
                if (!items.length) {
                    suggestionNames = [];
                    activeSuggestionIndex = -1;
                    suggestionWrap.innerHTML = '<div class="px-3 py-2 text-secondary small">Produk tidak ditemukan.</div>';
                    suggestionWrap.classList.remove('d-none');
                    return;
                }

                suggestionNames = items;
                activeSuggestionIndex = -1;
                suggestionWrap.innerHTML = items.map((name) => `
                    <button type="button" class="w-100 text-start border-0 bg-white px-3 py-2 border-bottom suggestion-item" data-name="${escapeHtml(name)}" role="option">
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
                syncBrandShortcutState();
                loadProducts({ reset: true });
            };

            const loadSuggestions = async () => {
                const keyword = keywordInput.value.trim();
                if (!canSearchKeyword(keyword)) {
                    clearSuggestions();
                    showHint(keyword.length > 0);
                    renderGridMessage('Produk akan ditampilkan setelah kata kedua diketik dan pilihan produk dipilih.');
                    return false;
                }

                const response = await fetch(`${suggestUrl}?keyword=${encodeURIComponent(keyword)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                const suggestionItems = payload.items || [];
                const exactMatch = suggestionItems.some((item) => item.toLowerCase() === keyword.toLowerCase());
                renderSuggestions(suggestionItems);

                if (!suggestionItems.length) {
                    showHint(false);
                    return false;
                }

                if (suggestionItems.length > 1 && !exactMatch) {
                    grid.innerHTML = '<div class="col-12 text-center text-secondary py-4">Pilih salah satu nama produk dari daftar saran.</div>';
                    hasMore = false;
                    updateLoadMoreVisibility();
                    showHint(true);
                    return true;
                }

                showHint(false);
                return false;
            };

            const loadProducts = async ({ reset = false } = {}) => {
                if (isLoading) {
                    return;
                }

                const nextPage = reset ? 1 : (currentPage + 1);
                if (!reset && !hasMore) {
                    return;
                }

                if (currentAbortController) {
                    currentAbortController.abort();
                }
                currentAbortController = new AbortController();

                const params = new URLSearchParams({
                    keyword: isSizeMode() ? '' : keywordInput.value.trim(),
                    size: isSizeMode() ? keywordInput.value.trim() : '',
                    exact: exactMode ? '1' : '0',
                    page: String(nextPage),
                });

                try {
                    setLoading(true);
                    showError(false);
                    const response = await fetch(`${searchUrl}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: currentAbortController.signal,
                    });

                    if (!response.ok) {
                        throw new Error('request_failed');
                    }

                    const payload = await response.json();
                    const sizeMode = isSizeMode() && canSearchBySize(keywordInput.value.trim());
                    const html = payload.items.map(renderCard).join('');
                    if (reset) {
                        if (sizeMode && !exactMode) {
                            renderShowcases(payload.showcases || []);
                            hasMore = false;
                        } else if (exactMode) {
                            grid.innerHTML = html || `<div class="col-12 text-center text-secondary py-4">${buildNoResultMessage()}</div>`;
                        } else {
                            renderGridMessage('Pilih salah satu nama produk dari daftar saran.');
                        }
                    } else {
                        grid.insertAdjacentHTML('beforeend', html);
                    }
                    currentPage = payload.pagination.current_page;
                    hasMore = payload.pagination.has_more;
                    updateLoadMoreVisibility();
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        showError(true);
                    }
                } finally {
                    setLoading(false);
                }
            };

            const triggerSearch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const keyword = keywordInput.value.trim();
                    const sizeMode = isSizeMode();

                    if (sizeMode) {
                        clearSuggestions();
                        showExactBadge(null);
                        if (!canSearchBySize(keyword)) {
                            renderShowcases([]);
                            hasMore = false;
                            updateLoadMoreVisibility();
                            return;
                        }
                        showHint(false);
                        loadProducts({ reset: true });
                        return;
                    }

                    if (!exactMode && !canSearchKeyword(keyword)) {
                        clearSuggestions();
                        showHint(keyword.length > 0);
                        renderGridMessage('Produk akan ditampilkan setelah kata kedua diketik dan pilihan produk dipilih.');
                        hasMore = false;
                        updateLoadMoreVisibility();
                        return;
                    }

                    if (!exactMode) {
                        loadSuggestions()
                            .then((hasSuggestion) => {
                                if (hasSuggestion) {
                                    renderGridMessage('Pilih salah satu nama produk dari daftar saran.');
                                } else {
                                    renderGridMessage(buildNoResultMessage());
                                }
                                hasMore = false;
                                updateLoadMoreVisibility();
                            })
                            .catch(() => {
                                showError(true);
                            });
                        return;
                    }

                    showHint(false);
                    loadProducts({ reset: true });
                }, 250);
            };

            keywordInput.addEventListener('input', () => {
                exactMode = false;
                showExactBadge(null);
                syncBrandShortcutState();
                triggerSearch();
            });
            keywordInput.addEventListener('focus', () => {
                searchControl.classList.add('is-focused');
            });
            keywordInput.addEventListener('blur', () => {
                searchControl.classList.remove('is-focused');
            });
            sizeModeSwitch.addEventListener('change', () => {
                exactMode = false;
                showExactBadge(null);
                keywordInput.value = '';
                clearSuggestions();
                syncBrandShortcutState();
                updateModeUI();
                triggerSearch();
            });
            voiceToggle.addEventListener('click', () => {
                if (!recognition) {
                    showError(true, 'Browser tidak mendukung fitur voice input.');
                    return;
                }

                try {
                    if (isRecording) {
                        recognition.stop();
                    } else {
                        recognition.lang = voiceLanguage;
                        recognition.start();
                    }
                } catch (error) {
                    showError(true, 'Voice input tidak dapat dijalankan saat ini.');
                }
            });
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
                    showHint(false);
                }
            });
            loadMoreButton.addEventListener('click', () => loadProducts({ reset: false }));
            suggestionWrap.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-name]');
                if (!button) {
                    return;
                }
                applySuggestion(button.dataset.name);
            });
            brandShortcutButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    keywordInput.value = `${button.dataset.brandKeyword || ''} `;
                    exactMode = false;
                    showExactBadge(null);
                    syncBrandShortcutState();
                    keywordInput.focus();
                    if (mobileMediaQuery.matches) {
                        isBrandShortcutsVisible = false;
                    }
                    syncBrandShortcutsVisibility();
                    triggerSearch();
                });
            });
            brandShortcutsToggle?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                isBrandShortcutsVisible = !isBrandShortcutsVisible;
                syncBrandShortcutsVisibility();
            });
            document.addEventListener('click', (event) => {
                if (!event.target.closest('#publicSearchForm .position-relative')) {
                    clearSuggestions();
                }
                if (!mobileMediaQuery.matches || isSizeMode() || !brandShortcutsToggle) {
                    return;
                }
                const target = event.target;
                const clickedInsidePanel = brandShortcutsWrap.contains(target);
                const clickedToggle = brandShortcutsToggle.contains(target);
                if (!clickedInsidePanel && !clickedToggle && isBrandShortcutsVisible) {
                    isBrandShortcutsVisible = false;
                    syncBrandShortcutsVisibility();
                }
            });

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    if (entries.some((entry) => entry.isIntersecting)) {
                        if (isSizeMode()) {
                            return;
                        }
                        if (!exactMode) {
                            return;
                        }
                        loadProducts({ reset: false });
                    }
                }, { rootMargin: '300px 0px' });
                observer.observe(sentinel);
            } else {
                updateLoadMoreVisibility();
            }

            sessionStorage.setItem('public_voice_lang', voiceLanguage);
            setupSpeechRecognition();
            setTimeout(() => {
                keywordInput.focus({ preventScroll: true });
                searchControl.classList.add('is-focused');
            }, 0);
            updateModeUI();
            updateVoiceStatus();
            syncBrandShortcutState();
            mobileMediaQuery.addEventListener('change', syncBrandShortcutsVisibility);
            syncBrandShortcutsVisibility();

        })();
    </script>
@endsection
