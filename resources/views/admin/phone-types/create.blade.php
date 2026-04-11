@extends('layouts.app')

@section('content')
    <h1 class="h5 mb-3">Tambah Etalase</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.phone-types.store') }}" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label">Nama Etalase</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label class="form-label">Ukuran Antigores</label>
                    <input type="text" name="antigores_size" class="form-control" value="{{ old('antigores_size') }}" placeholder="Contoh: 161 x 71" required>
                </div>
                <div>
                    <label class="form-label">Bentuk Kamera</label>
                    <input type="text" name="camera_shape" class="form-control" value="{{ old('camera_shape') }}" placeholder="Contoh: Boba, iPhone, Vertikal" required>
                </div>
                <div>
                    <label class="form-label">Link Belanja</label>
                    <input type="url" name="shopping_link" class="form-control" value="{{ old('shopping_link') }}" placeholder="https://...">
                </div>
                <div>
                    <label class="form-label">Masteran</label>
                    <textarea name="masteran" class="form-control" rows="3" placeholder="Isi masteran">{{ old('masteran') }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Simpan</button>
                    <a href="{{ route('admin.phone-types.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
