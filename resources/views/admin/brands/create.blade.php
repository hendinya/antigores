@extends('layouts.app')

@section('content')
    <h1 class="h5 mb-3">Tambah Brand</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.brands.store') }}" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label">Master Brand</label>
                    <select id="masterBrandSelect" name="master_brand_id" class="form-select" data-placeholder="Pilih brand" required>
                        <option value="">Pilih brand</option>
                        @foreach($masterBrands as $masterBrand)
                            <option value="{{ $masterBrand->id }}" @selected(old('master_brand_id') == $masterBrand->id)>{{ $masterBrand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select id="categorySelect" name="category_id" class="form-select" data-placeholder="Pilih kategori" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Simpan</button>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
