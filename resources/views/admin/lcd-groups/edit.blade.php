@extends('layouts.app')

@section('content')
    @php($returnTo = request('return_to', route('admin.lcd-groups.index')))
    @php($selectedMasterIds = collect(old('product_master_ids', $lcdGroup->productMasters->pluck('id')->all()))->map(fn ($value) => (string) $value)->all())
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
                    <select name="product_master_ids[]" class="form-select select2" multiple required data-placeholder="Pilih produk">
                        @foreach($productMasters as $master)
                            <option value="{{ $master['id'] }}" @selected(in_array((string) $master['id'], $selectedMasterIds, true))>{{ $master['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="small text-secondary">
                    Produk dari grup lain tidak dapat ditambahkan ke grup ini.
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
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Pilih produk',
            });
        })();
    </script>
@endsection
