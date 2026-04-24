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
                    <select name="product_master_ids[]" class="form-select select2" multiple required data-placeholder="Pilih produk">
                        @foreach($productMasters as $master)
                            <option value="{{ $master['id'] }}" @selected(collect(old('product_master_ids', []))->contains((string) $master['id']) || collect(old('product_master_ids', []))->contains($master['id']))>{{ $master['label'] }}</option>
                        @endforeach
                    </select>
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
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Pilih produk',
            });
        })();
    </script>
@endsection
