@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Etalase</h1>
        <a href="{{ route('admin.phone-types.create') }}" class="btn btn-dark btn-sm">Tambah</a>
    </div>

    <form method="GET" action="{{ route('admin.phone-types.index') }}" class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="input-group">
                <input type="text" id="phoneTypesKeywordInput" name="keyword" class="form-control" value="{{ $keyword }}" placeholder="Cari nama etalase atau ukuran antigores">
                <button class="btn btn-dark" type="submit">Cari</button>
            </div>
            <div class="d-flex gap-2 mt-2 flex-wrap">
                <a href="{{ route('admin.phone-types.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                <a href="{{ route('admin.phone-types.template') }}" class="btn btn-outline-secondary btn-sm ms-md-auto">Download Template</a>
                <a href="{{ route('admin.phone-types.export-filtered', request()->query()) }}" class="btn btn-outline-dark btn-sm" id="phoneTypesExportFilteredBtn" data-base-export-url="{{ route('admin.phone-types.export-filtered') }}">Download Data Filter</a>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.phone-types.import') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label class="form-label">Import Excel Etalase</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx" required>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-dark w-100">Import Etalase</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Nama Etalase</th>
                    <th>Bentuk Kamera</th>
                    <th>Ukuran Antigores</th>
                    <th>Masteran</th>
                    <th>Link Belanja</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($phoneTypes as $phoneType)
                    <tr>
                        <td>{{ $phoneType->name }}</td>
                        <td>{{ $phoneType->camera_shape ?? '-' }}</td>
                        <td>{{ $phoneType->antigores_size ?? '-' }}</td>
                        <td>{{ $phoneType->masteran ?? '-' }}</td>
                        <td>
                            @if($phoneType->shopping_link)
                                <a href="{{ $phoneType->shopping_link }}" target="_blank" rel="noopener noreferrer" title="{{ $phoneType->shopping_link }}">{{ \Illuminate\Support\Str::limit($phoneType->shopping_link, 25) }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.phone-types.edit', ['phone_type' => $phoneType, 'return_to' => url()->full()]) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.phone-types.destroy', $phoneType) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm btn-delete-cascade"
                                    data-product-count="{{ $phoneType->products_count }}"
                                    data-phone-type-name="{{ $phoneType->name }}"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-secondary">Belum ada etalase.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $phoneTypes->links() }}</div>
    <script>
        (() => {
            const keywordInput = document.getElementById('phoneTypesKeywordInput');
            const exportFilteredButton = document.getElementById('phoneTypesExportFilteredBtn');

            const syncExportFilteredUrl = () => {
                if (!(exportFilteredButton instanceof HTMLAnchorElement) || !(keywordInput instanceof HTMLInputElement)) {
                    return;
                }
                const params = new URLSearchParams({ keyword: keywordInput.value.trim() });
                exportFilteredButton.href = `${exportFilteredButton.dataset.baseExportUrl}?${params.toString()}`;
            };

            keywordInput?.addEventListener('input', syncExportFilteredUrl);
            syncExportFilteredUrl();

            document.addEventListener('click', async (event) => {
                const trigger = event.target.closest('.btn-delete-cascade');
                if (!trigger) {
                    return;
                }

                const form = trigger.closest('form');
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                const productCount = Number(trigger.getAttribute('data-product-count') || '0');
                const phoneTypeName = trigger.getAttribute('data-phone-type-name') || 'etalase ini';
                const infoText = productCount > 0
                    ? `Etalase "${phoneTypeName}" akan dihapus beserta ${productCount} produk terkait.`
                    : `Etalase "${phoneTypeName}" akan dihapus.`;

                const confirmation = await Swal.fire({
                    icon: 'warning',
                    title: 'Konfirmasi Hapus Berantai',
                    text: infoText,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });

                if (!confirmation.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });
                form.submit();
            });
        })();
    </script>
@endsection
