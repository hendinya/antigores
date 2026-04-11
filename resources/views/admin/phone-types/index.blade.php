@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Etalase</h1>
        <a href="{{ route('admin.phone-types.create') }}" class="btn btn-dark btn-sm">Tambah</a>
    </div>

    <form method="GET" action="{{ route('admin.phone-types.index') }}" class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="Cari nama etalase atau ukuran antigores">
                <button class="btn btn-dark" type="submit">Cari</button>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Nama Etalase</th>
                    <th>Bentuk Kamera</th>
                    <th>Ukuran Antigores</th>
                    <th>Masteran</th>
                    <th>Link Belanja</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($phoneTypes as $phoneType)
                    <tr>
                        <td>{{ $phoneType->name }}</td>
                        <td>{{ $phoneType->camera_shape ?? '-' }}</td>
                        <td>{{ $phoneType->antigores_size ?? '-' }}</td>
                        <td>{{ $phoneType->masteran ?? '-' }}</td>
                        <td>
                            @if($phoneType->shopping_link)
                                <a href="{{ $phoneType->shopping_link }}" target="_blank" rel="noopener noreferrer" title="{{ $phoneType->shopping_link }}">{{ \Illuminate\Support\Str::limit($phoneType->shopping_link, 25) }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.phone-types.edit', $phoneType) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.phone-types.destroy', $phoneType) }}" class="d-inline" data-confirm-delete>
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-secondary">Belum ada etalase.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $phoneTypes->links() }}</div>
@endsection
