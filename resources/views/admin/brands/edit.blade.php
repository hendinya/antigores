@extends('layouts.app')

@section('content')
    @php($returnTo = request('return_to', route('admin.brands.index')))
    <h1 class="h5 mb-3">Edit Brand</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.brands.update', $brand) }}" class="vstack gap-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ $returnTo }}">
                <div>
                    <label class="form-label">Master Brand</label>
                    <select id="masterBrandSelect" name="master_brand_id" class="form-select" data-placeholder="Pilih master brand" required>
                        <option value="">Pilih master brand</option>
                        @foreach($masterBrands as $masterBrand)
                            <option value="{{ $masterBrand->id }}" @selected(old('master_brand_id', $selectedMasterBrandId) == $masterBrand->id)>{{ $masterBrand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select id="categorySelect" name="category_id" class="form-select" data-placeholder="Pilih kategori" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $brand->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Update</button>
                    <a href="{{ $returnTo }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
