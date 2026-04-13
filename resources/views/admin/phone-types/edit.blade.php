@extends('layouts.app')

@section('content')
    @php($returnTo = request('return_to', route('admin.phone-types.index')))
    <h1 class="h5 mb-3">Edit Etalase</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.phone-types.update', $phoneType) }}" class="vstack gap-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ $returnTo }}">
                <div>
                    <label class="form-label">Nama Etalase</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $phoneType->name) }}" required>
                </div>
                <div>
                    <label class="form-label">Ukuran Antigores</label>
                    <input type="text" name="antigores_size" class="form-control" value="{{ old('antigores_size', $phoneType->antigores_size) }}" placeholder="Contoh: 6.7 inci" required>
                </div>
                <div>
                    <label class="form-label">Bentuk Kamera</label>
                    <input type="text" name="camera_shape" class="form-control" value="{{ old('camera_shape', $phoneType->camera_shape) }}" placeholder="Contoh: Boba, iPhone, Vertikal" required>
                </div>
                <div>
                    <label class="form-label">Link Belanja</label>
                    <input type="url" name="shopping_link" class="form-control" value="{{ old('shopping_link', $phoneType->shopping_link) }}" placeholder="https://...">
                </div>
                <div>
                    <label class="form-label">Masteran</label>
                    <textarea name="masteran" class="form-control" rows="3" placeholder="Isi masteran">{{ old('masteran', $phoneType->masteran) }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Update</button>
                    <a href="{{ $returnTo }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
