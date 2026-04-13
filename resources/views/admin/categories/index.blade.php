@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Kategori</h1>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-dark btn-sm">Tambah</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            @if($category->image_path)
                                <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->name }}" class="rounded" style="width: 42px; height: 42px; object-fit: cover;">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $category->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.categories.edit', ['category' => $category, 'return_to' => url()->full()]) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline" data-confirm-delete>
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                <button class="btn btn-outline-danger btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-4 text-secondary">Belum ada kategori.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $categories->links() }}</div>
@endsection
