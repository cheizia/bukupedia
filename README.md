# Bukupedia

Bukupedia adalah REST API sistem perpustakaan digital yang dibuat menggunakan Laravel, MySQL, dan Laravel Sanctum.

## Fitur

- Register dan login
- Role Admin, Petugas, Anggota
- CRUD Buku
- CRUD Kategori
- Peminjaman buku
- Pengembalian buku
- Denda otomatis Rp2.000/hari
- Maksimal 3 buku aktif
- Stok otomatis berkurang/bertambah
- Riwayat peminjaman

## Instalasi

Clone project:

git clone <url-repository>

Masuk ke folder:
cd bukupedia
Install dependency:
composer install
Copy environment:
copy .env.example .env
Generate key:
php artisan key:generate
Install API:
php artisan install:api
Jalankan migration:
php artisan migrate
Jalankan server:
php artisan serve

## Konfigurasi Database

Buat database:
bukupedia

Atur `.env`:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bukupedia
DB_USERNAME=root
DB_PASSWORD=

## Endpoint Authentication

POST /api/register
POST /api/login
POST /api/logout
GET /api/user

## Endpoint Buku

GET /api/buku
GET /api/buku/{id}
POST /api/buku
PUT /api/buku/{id}
DELETE /api/buku/{id}

## Endpoint Kategori

GET /api/kategori
GET /api/kategori/{id}
POST /api/kategori
PUT /api/kategori/{id}
DELETE /api/kategori/{id}

## Endpoint Peminjaman

POST /api/peminjaman/pinjam
GET /api/peminjaman/riwayat
PUT /api/peminjaman/{id}/kembali

## Hak Akses

Admin:
- CRUD Buku
- CRUD Kategori
- Melihat seluruh riwayat
- Memproses pengembalian

Petugas:
- Melihat riwayat
- Memproses pengembalian

Anggota:
- Melihat buku
- Meminjam buku
- Melihat riwayat sendiri

## Business Logic

- Buku hanya dapat dipinjam jika stok > 0
- Maksimal 3 peminjaman aktif per anggota
- Masa pinjam 7 hari
- Denda Rp2.000 per hari keterlambatan
- Stok berkurang saat buku dipinjam
- Stok bertambah saat buku dikembalikan
- Password disimpan dalam bentuk hash
- Register otomatis mendapatkan role anggota

## Authentication

Gunakan Bearer Token dari hasil login:
Authorization: Bearer TOKEN

## Testing

API dapat diuji menggunakan Postman.
Base URL:
http://127.0.0.1:8000/api

## Version Control

git init
git add .
git commit -m "initial project"
Gunakan beberapa commit selama proses pengerjaan.