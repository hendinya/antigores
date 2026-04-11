@extends('layouts.app')

@section('content')
    <h1 class="h5 mb-3">Tambah Master Brand</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.master-brands.store') }}" enctype="multipart/form-data" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label">Nama Master Brand</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label class="form-label">Gambar Brand</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Simpan</button>
                    <a href="{{ route('admin.master-brands.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
