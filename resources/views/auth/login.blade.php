@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h5 mb-3">Login Affiliator</h1>
                    <form method="POST" action="{{ route('login.store') }}" class="vstack gap-3">
                        @csrf
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div>
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        <button class="btn btn-dark w-100">Login Affiliator</button>
                    </form>
                    <a href="{{ route('google.redirect') }}" class="btn btn-outline-danger w-100 mt-2">Login dengan Google</a>
                    <p class="small text-secondary mt-3 mb-0">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
