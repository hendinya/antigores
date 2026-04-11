@extends('layouts.app')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Sistem Manajemen Produk Antigores HP</h1>
            <p class="text-secondary mb-4">Kelola kategori, merk, etalase, dan produk antigores secara cepat dan sederhana.</p>
            <div class="d-flex gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-primary">Lihat Produk</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark">Kelola Admin</a>
                @else
                    <a href="{{ route('member.showcases.edit') }}" class="btn btn-outline-dark">Atur Etalase</a>
                @endif
            </div>
        </div>
    </div>

    @if(!auth()->user()->isAdmin())
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Panduan Member: Atur Etalase untuk Live TikTok</h2>
                <p class="text-secondary mb-3">Website ini dibuat untuk menyamakan data produk dengan posisi etalase fisik saat Anda live streaming jualan, supaya proses ambil barang lebih cepat dan minim salah ambil.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h3 class="h6 mb-2">Langkah 1: Atur Etalase Member</h3>
                            <ol class="mb-0 text-secondary small ps-3">
                                <li class="mb-1">Buka menu Atur Etalase.</li>
                                <li class="mb-1">Pilih kategori produk.</li>
                                <li class="mb-1">Isi nomor posisi etalase untuk tiap brand.</li>
                                <li>Simpan data sampai semua brand yang dipakai live sudah terisi.</li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h3 class="h6 mb-2">Langkah 2: Gunakan Saat Live</h3>
                            <ol class="mb-0 text-secondary small ps-3">
                                <li class="mb-1">Buka menu Products saat live berjalan.</li>
                                <li class="mb-1">Cari nama produk atau filter kategori dan brand.</li>
                                <li class="mb-1">Lihat informasi Posisi Etalase pada kartu produk.</li>
                                <li>Ambil barang sesuai posisi etalase agar pelayanan live lebih cepat.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    Jika Posisi Etalase menampilkan “Etalase belum di atur”, lengkapi dulu data di menu Atur Etalase sebelum live.
                </div>
            </div>
        </div>
    @endif
@endsection
