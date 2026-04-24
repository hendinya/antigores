@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Grup LCD</h1>
        <a href="{{ route('admin.lcd-groups.create') }}" class="btn btn-dark btn-sm">Tambah Grup</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Nama Grup</th>
                    <th>Catatan</th>
                    <th>Jumlah Produk</th>
                    <th>Produk Dalam Grup</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($groups as $group)
                    @php
                        $masterNames = $group->productMasters
                            ->map(fn ($master) => trim($master->name).' ('.($master->brand->name ?? '-').')')
                            ->take(3)
                            ->implode(', ');
                    @endphp
                    <tr>
                        <td>{{ $group->name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($group->note, 80) ?: '-' }}</td>
                        <td><span class="badge text-bg-secondary">{{ $group->product_masters_count }}</span></td>
                        <td>
                            {{ $masterNames !== '' ? $masterNames : '-' }}
                            @if($group->product_masters_count > 3)
                                <span class="text-secondary">+{{ $group->product_masters_count - 3 }} lainnya</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.lcd-groups.edit', ['lcd_group' => $group, 'return_to' => url()->full()]) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.lcd-groups.destroy', $group) }}" class="d-inline" data-confirm-delete>
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                <button class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-secondary">Belum ada grup LCD.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $groups->links() }}</div>
@endsection
