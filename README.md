# AntiGores

Aplikasi manajemen katalog produk anti-gores berbasis Laravel, dengan dua mode utama:

- katalog publik (`/offline-products`)
- katalog affiliator/login (`/products`)

## Dependensi

### Runtime

- PHP 8.2+
- Composer 2.x
- MySQL/MariaDB
- Node.js 20+ dan npm

### Library utama

- Laravel Framework (v13.x)
- OpenSpout (import/export Excel)
- SweetAlert2 (UI alert/konfirmasi)
- Bootstrap 5.3

## Menjalankan Lokal

1) Clone repository

```bash
git clone https://github.com/hendinya/antigores.git
cd antigores
```

2) Install dependency backend

```bash
composer install
```

3) Install dependency frontend

```bash
npm install
```

4) Siapkan environment

```bash
cp .env.example .env
php artisan key:generate
```

5) Atur koneksi database pada `.env` lalu migrasi

```bash
php artisan migrate
php artisan db:seed
```

6) Build asset frontend

```bash
npm run build
```

7) Jalankan aplikasi

```bash
php artisan serve
```

Akses di: `http://127.0.0.1:8000`

## Testing dan Quality Check

Jalankan test:

```bash
php artisan test
```

Jalankan pengecekan format kode:

```bash
php vendor/bin/pint --test
```

## Struktur File Environment

- `.env` tidak di-commit (sudah di-ignore)
- `.env.example` disediakan sebagai template konfigurasi

## Deployment Repository

Source code utama didorong ke branch `main` pada:

`https://github.com/hendinya/antigores.git`

Untuk deployment server (Render/Railway/VPS) gunakan source ini dan ikuti langkah:

- set environment variable server
- `composer install --no-dev --optimize-autoloader`
- `php artisan migrate --force`
- `npm run build` (atau build saat CI)

Panduan deploy VPS domain `tg.hpulsa.com`:

`deploy/vps/VPS_DEPLOY.md`

## Catatan GitHub Pages

GitHub Pages hanya untuk static site, sedangkan aplikasi ini membutuhkan runtime PHP + database.  
Karena itu deployment aplikasi penuh direkomendasikan menggunakan service PHP hosting atau VPS.
