@extends('layouts.app')

@section('content')
    <h1 class="h5 mb-3">Edit Brand</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="vstack gap-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label">Nama Brand</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $brand->name) }}" required>
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $brand->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Gambar Brand</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if($brand->image_path)
                        <img src="{{ asset('storage/'.$brand->image_path) }}" alt="{{ $brand->name }}" class="img-thumbnail mt-2" style="max-width: 120px;">
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Update</button>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
