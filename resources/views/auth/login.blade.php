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
                    <a href="{{ route('google.redirect') }}" class="btn btn-outline-danger w-100 mt-2 d-inline-flex align-items-center justify-content-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                            <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.656 32.657 29.26 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.84 1.154 7.953 3.047l5.657-5.657C34.046 6.053 29.27 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                            <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.84 1.154 7.953 3.047l5.657-5.657C34.046 6.053 29.27 4 24 4c-7.681 0-14.318 4.337-17.694 10.691z"/>
                            <path fill="#4CAF50" d="M24 44c5.167 0 9.86-1.977 13.409-5.197l-6.191-5.238C29.149 35.091 26.715 36 24 36c-5.239 0-9.624-3.331-11.283-7.946l-6.522 5.025C9.52 39.556 16.227 44 24 44z"/>
                            <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.236 4.166-4.085 5.565l.003-.002 6.191 5.238C36.97 39.454 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                        </svg>
                        <span>Login dengan Google</span>
                    </a>
                    <p class="small text-secondary mt-3 mb-0">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
