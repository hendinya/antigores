@extends('layouts.app')

@section('content')
    <h1 class="h5 mb-3">Atur Etalase</h1>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <ul class="nav nav-tabs">
                @foreach($categories as $category)
                    <li class="nav-item">
                        <a href="{{ route('member.showcases.edit', ['category_id' => $category->id]) }}" class="nav-link {{ $selectedCategoryId == $category->id ? 'active' : '' }}">{{ $category->name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('member.showcases.update') }}" class="vstack gap-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th style="width: 40%">Brand</th>
                            <th>Nomor Etalase</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($brands as $brand)
                            <tr>
                                <td class="fw-semibold">{{ $brand->name }}</td>
                                <td>
                                    <input type="number" min="1" inputmode="numeric" name="showcases[{{ $brand->id }}]" class="form-control" value="{{ old('showcases.'.$brand->id, $showcases[$brand->id] ?? '') }}" placeholder="Contoh: 78">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-secondary">Belum ada brand untuk kategori ini.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-dark"><span aria-hidden="true">💾</span> Simpan</button>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
