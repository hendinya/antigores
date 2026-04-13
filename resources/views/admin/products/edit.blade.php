@extends('layouts.app')

@section('content')
    @php($returnTo = request('return_to', route('admin.products.index')))
    <h1 class="h5 mb-3">Edit Produk</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" class="vstack gap-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ $returnTo }}">
                <div>
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Brand</label>
                    <select id="brand_id" name="brand_id" class="form-select" required>
                        <option value="">Pilih brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Etalase</label>
                    <select name="phone_type_id" class="form-select" required>
                        <option value="">Pilih etalase</option>
                        @foreach($phoneTypes as $phoneType)
                            <option value="{{ $phoneType->id }}" @selected(old('phone_type_id', $product->phone_type_id) == $phoneType->id)>{{ $phoneType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Catatan Produk</label>
                    <textarea name="product_note" class="form-control" rows="3" placeholder="Isi catatan produk">{{ old('product_note', $product->product_note) }}</textarea>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="uiverse-switch">
                        <input type="hidden" name="is_visible_for_affiliator" value="0">
                        <input class="visibility-switch" type="checkbox" role="switch" id="is_visible_for_affiliator" name="is_visible_for_affiliator" value="1" @checked(old('is_visible_for_affiliator', $product->is_visible_for_affiliator))>
                        <span class="uiverse-slider"></span>
                    </label>
                    <label class="form-label mb-0" for="is_visible_for_affiliator">Tampilkan di halaman affiliator (/products)</label>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark">Update</button>
                    <a href="{{ $returnTo }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
