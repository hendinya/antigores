@extends('layouts.app')

@section('content')
    @php($returnTo = request('return_to', route('admin.categories.index')))
    <h1 class="h5 mb-3">Edit Kategori</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="vstack gap-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ $returnTo }}">
                <div>
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                </div>
                <div>
                    <label class="form-label">Gambar Kategori</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if($category->image_path)
                        <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->name }}" class="img-thumbnail mt-2" style="max-width: 120px;">
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Update</button>
                    <a href="{{ $returnTo }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
