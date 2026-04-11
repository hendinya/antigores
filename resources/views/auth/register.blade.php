@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h5 mb-3">Register</h1>
                    <form method="POST" action="{{ route('register.store') }}" class="vstack gap-3">
                        @csrf
                        <div>
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div>
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button class="btn btn-dark w-100">Daftar</button>
                    </form>
                    <p class="small text-secondary mt-3 mb-0">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
