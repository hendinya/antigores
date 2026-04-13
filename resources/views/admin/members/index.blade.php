@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Member Terdaftar</h1>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.members.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Cari Member</label>
                    <input type="text" name="keyword" class="form-control" value="{{ $keyword }}" placeholder="Nama atau email">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button class="btn btn-dark btn-sm">Filter</button>
                    <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Terdaftar</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($members as $member)
                    <tr>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->email }}</td>
                        <td><span class="badge text-bg-secondary">{{ $member->role }}</span></td>
                        <td>{{ $member->created_at?->format('d M Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.members.edit', ['member' => $member, 'return_to' => url()->full()]) }}" class="btn btn-outline-primary btn-sm">Kelola</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-secondary">Belum ada member terdaftar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $members->links() }}</div>
@endsection
