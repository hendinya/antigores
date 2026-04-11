<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Manajemen Antigores' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --app-bg-start: #f4f8ff;
            --app-bg-end: #eef2f7;
            --app-surface: #ffffff;
            --app-surface-soft: #f8fbff;
            --app-text: #1f2937;
            --app-text-soft: #4b5563;
            --app-primary: #2f6fdd;
            --app-primary-soft: rgba(47, 111, 221, .12);
            --app-border: rgba(15, 23, 42, .1);
            --app-radius: 12px;
            --app-radius-sm: 10px;
            --app-shadow: 0 10px 28px rgba(15, 23, 42, .08);
            --app-transition: 240ms ease;
        }
        html {
            min-width: 320px;
        }
        body {
            font-family: Inter, Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--app-text);
            background: linear-gradient(180deg, var(--app-bg-start) 0%, #f9fbff 48%, var(--app-bg-end) 100%);
            overflow-x: hidden;
        }
        .navbar {
            background: linear-gradient(135deg, #215ec6 0%, #2f6fdd 35%, #4e8bf1 100%) !important;
            box-shadow: 0 8px 24px rgba(33, 94, 198, .28);
        }
        .navbar .nav-link {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            border-radius: 10px;
            transition: background-color var(--app-transition), color var(--app-transition);
        }
        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            background-color: rgba(255, 255, 255, .18);
        }
        .card {
            background: var(--app-surface);
            border: 1px solid var(--app-border) !important;
            border-radius: var(--app-radius) !important;
            box-shadow: var(--app-shadow);
        }
        .card-header {
            background: var(--app-surface-soft) !important;
            border-bottom: 1px solid var(--app-border);
            border-top-left-radius: calc(var(--app-radius) - 1px) !important;
            border-top-right-radius: calc(var(--app-radius) - 1px) !important;
        }
        .btn,
        .form-control,
        .form-select,
        .input-group-text,
        .form-check-input {
            min-height: 44px;
            border-radius: var(--app-radius-sm);
            transition: box-shadow var(--app-transition), transform var(--app-transition), background-color var(--app-transition), border-color var(--app-transition), color var(--app-transition);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            line-height: 1.2;
            white-space: nowrap;
        }
        .btn-sm {
            min-height: 40px;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn:active {
            transform: translateY(0);
        }
        .btn-dark {
            background: linear-gradient(135deg, #1f57b5 0%, #2f6fdd 100%);
            border-color: #2f6fdd;
        }
        .btn-outline-secondary {
            color: #374151;
            border-color: #cfd7e3;
        }
        .form-control,
        .form-select {
            border-color: #d5deea;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #7ea5eb;
            box-shadow: 0 0 0 .2rem rgba(47, 111, 221, .16);
        }
        .badge {
            border-radius: 999px;
        }
        .uiverse-switch {
            --switch-w: 52px;
            --switch-h: 28px;
            --thumb: 22px;
            position: relative;
            width: var(--switch-w);
            height: var(--switch-h);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .uiverse-switch input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }
        .uiverse-switch .uiverse-slider {
            position: relative;
            width: var(--switch-w);
            height: var(--switch-h);
            border-radius: 999px;
            background: linear-gradient(135deg, #e2e8f0 0%, #cfd8e5 100%);
            border: 1px solid rgba(15, 23, 42, .12);
            box-shadow: inset 0 2px 4px rgba(15, 23, 42, .08);
            transition: background-color 240ms ease, border-color 240ms ease, box-shadow 240ms ease;
            pointer-events: none;
        }
        .uiverse-switch .uiverse-slider::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: var(--thumb);
            height: var(--thumb);
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, .22);
            transition: transform 240ms ease, background-color 240ms ease;
        }
        .uiverse-switch input:checked + .uiverse-slider {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-color: rgba(37, 99, 235, .6);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12), 0 0 0 .18rem rgba(37, 99, 235, .18);
        }
        .uiverse-switch input:checked + .uiverse-slider::before {
            transform: translateX(24px);
            background: #fff;
        }
        .uiverse-switch input:focus-visible + .uiverse-slider {
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .2);
        }
        .uiverse-switch input:disabled + .uiverse-slider {
            opacity: .55;
        }
        table.table {
            margin-bottom: 0;
        }
        table.table th,
        table.table td {
            vertical-align: middle;
            color: var(--app-text);
        }
        img {
            max-width: 100%;
            height: auto;
        }
        @media (max-width: 991.98px) {
            .navbar .container {
                max-width: 100%;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .navbar-toggler {
                width: 44px;
                height: 44px;
                padding: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                border: 1px solid rgba(255, 255, 255, .45);
            }
            .navbar-toggler-icon {
                margin: 0 auto;
            }
            .navbar-collapse {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1060;
                width: min(82vw, 340px);
                max-width: 100%;
                overflow-y: auto;
                background: linear-gradient(180deg, #1f57b5 0%, #2f6fdd 50%, #4e8bf1 100%);
                backdrop-filter: blur(10px);
                border-radius: 0 16px 16px 0;
                margin-top: 0;
                padding: 1rem .75rem;
                transform: translateX(-104%);
                transition: transform 260ms ease;
                box-shadow: 0 18px 40px rgba(15, 23, 42, .32);
            }
            .navbar-collapse.show,
            .navbar-collapse.collapsing {
                transform: translateX(0);
            }
            .navbar-collapse.collapsing {
                display: block;
                height: auto !important;
            }
            .navbar-collapse {
                background: linear-gradient(180deg, #1f57b5 0%, #2f6fdd 50%, #4e8bf1 100%);
            }
            .navbar-nav.ms-auto {
                margin-top: .5rem;
                border-top: 1px solid rgba(255, 255, 255, .18);
                padding-top: .5rem;
            }
            .mobile-nav-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, .42);
                backdrop-filter: blur(2px);
                z-index: 1055;
                opacity: 0;
                pointer-events: none;
                transition: opacity 260ms ease;
            }
            .mobile-nav-backdrop.is-visible {
                opacity: 1;
                pointer-events: auto;
            }
            .navbar .nav-link {
                width: 100%;
                padding-left: .75rem;
                padding-right: .75rem;
            }
        }
        @media (max-width: 767.98px) {
            main.container {
                max-width: 100%;
                padding-left: .75rem;
                padding-right: .75rem;
            }
            table.table thead {
                display: none;
            }
            table.table,
            table.table tbody,
            table.table tr,
            table.table td {
                display: block;
                width: 100%;
            }
            table.table tbody tr {
                margin: .6rem 0;
                border: 1px solid var(--app-border);
                border-radius: 12px;
                padding: .4rem .7rem;
                background: #fff;
                box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
            }
            table.table tbody td {
                border: 0;
                border-bottom: 1px dashed rgba(15, 23, 42, .12);
                text-align: left !important;
                padding: .65rem .25rem;
                font-size: .93rem;
            }
            table.table tbody td:last-child {
                border-bottom: 0;
                padding-bottom: .35rem;
            }
            table.table tbody td::before {
                content: attr(data-label);
                display: block;
                font-size: .75rem;
                font-weight: 700;
                color: #4b5563;
                margin-bottom: .2rem;
                text-transform: uppercase;
                letter-spacing: .03em;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ auth()->check() ? route('home') : route('public.products.index') }}">AntiGores</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('public.products.*') ? 'active' : '' }}" href="{{ route('public.products.index') }}">Katalog Publik</a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a>
                    </li>
                    @if(!auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('member.showcases.*') ? 'active' : '' }}" href="{{ route('member.showcases.edit') }}">Atur Etalase</a>
                        </li>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}" href="{{ route('admin.brands.index') }}">Brands</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.phone-types.*') ? 'active' : '' }}" href="{{ route('admin.phone-types.index') }}">Etalase</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">Admin Products</a>
                        </li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto">
                @guest
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Login Affiliator</a>
                    </li>
                @else
                    <li class="nav-item d-flex align-items-center text-white-50 me-3 small">
                        {{ auth()->user()->name }} ({{ auth()->user()->role }})
                    </li>
                    <li class="nav-item d-flex align-items-center">
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button class="btn btn-outline-light btn-sm">Logout</button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
<div id="mobileNavBackdrop" class="mobile-nav-backdrop d-lg-none"></div>

<main class="container pb-5">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (() => {
        const flashSuccess = @json(session('success'));
        const flashError = @json(session('error'));
        const validationErrors = @json($errors->all());

        const toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });

        const showLoading = (title = 'Memproses...') => {
            Swal.fire({
                title,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading(),
            });
        };

        if (flashSuccess) {
            toast.fire({ icon: 'success', title: flashSuccess });
        }

        if (flashError) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: flashError,
                confirmButtonText: 'OK',
            });
        }

        if (validationErrors.length) {
            const errorsHtml = `<ul class="text-start mb-0 ps-3">${validationErrors.map((error) => `<li>${error}</li>`).join('')}</ul>`;
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                html: errorsHtml,
                confirmButtonText: 'Perbaiki',
            });
        }

        const confirmDelete = (form) => {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Data?',
                text: 'Apakah Anda yakin ingin menghapus data ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                form.dataset.confirmedDelete = '1';
                showLoading('Menghapus...');
                form.submit();
            });
        };

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('.btn-delete');
            if (!trigger) {
                return;
            }

            const form = trigger.closest('form');
            if (!form) {
                return;
            }

            event.preventDefault();
            confirmDelete(form);
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form.matches('form[data-confirm-delete]') && form.dataset.confirmedDelete !== '1') {
                event.preventDefault();
                confirmDelete(form);
                return;
            }

            if (form.method.toLowerCase() === 'get' || form.dataset.loadingShown === '1') {
                return;
            }

            form.dataset.loadingShown = '1';
            showLoading();
        });

        const setResponsiveTableLabels = (scope = document) => {
            scope.querySelectorAll('table.table').forEach((table) => {
                const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
                table.querySelectorAll('tbody tr').forEach((tr) => {
                    Array.from(tr.children).forEach((cell, index) => {
                        if (cell instanceof HTMLTableCellElement) {
                            cell.setAttribute('data-label', headers[index] ?? '');
                        }
                    });
                });
            });
        };

        setResponsiveTableLabels();

        document.querySelectorAll('table.table tbody').forEach((tbody) => {
            const observer = new MutationObserver(() => setResponsiveTableLabels(document));
            observer.observe(tbody, { childList: true, subtree: true });
        });

        document.querySelectorAll('img').forEach((image) => {
            if (!image.getAttribute('loading')) {
                image.setAttribute('loading', 'lazy');
            }
            if (!image.getAttribute('decoding')) {
                image.setAttribute('decoding', 'async');
            }
        });

        const mobileNavbar = document.getElementById('mainNavbar');
        const mobileNavBackdrop = document.getElementById('mobileNavBackdrop');
        if (mobileNavbar && mobileNavBackdrop && window.bootstrap?.Collapse) {
            const mobileNavCollapse = bootstrap.Collapse.getOrCreateInstance(mobileNavbar, { toggle: false });
            const closeMobileNav = () => {
                if (mobileNavbar.classList.contains('show')) {
                    mobileNavCollapse.hide();
                }
            };
            mobileNavbar.addEventListener('show.bs.collapse', () => {
                mobileNavBackdrop.classList.add('is-visible');
            });
            mobileNavbar.addEventListener('hidden.bs.collapse', () => {
                mobileNavBackdrop.classList.remove('is-visible');
            });
            mobileNavBackdrop.addEventListener('click', closeMobileNav);
            mobileNavbar.querySelectorAll('.nav-link').forEach((link) => {
                link.addEventListener('click', closeMobileNav);
            });
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 992) {
                    mobileNavBackdrop.classList.remove('is-visible');
                    mobileNavCollapse.hide();
                }
            });
        }
    })();
</script>
</body>
</html>
