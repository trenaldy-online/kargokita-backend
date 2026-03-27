# KargoKita Backend

Backend untuk layanan cek ongkir, manajemen rute, dan produksi konten SEO di **KargoKita**. Aplikasi ini dibangun dengan **Laravel 12** dan **Filament 3** untuk mengelola tarif ekspedisi, artikel blog manual, artikel rute berbasis AI, serta gambar AI untuk kebutuhan landing page dan katalog rute.

## Fitur Utama

- Cek ongkir berdasarkan kota asal dan kota tujuan.
- Manajemen data rute dan tarif melalui panel admin Filament.
- Import data rute via CSV.
- Pembuatan artikel SEO berbasis AI secara batch.
- Pembuatan gambar rute berbasis AI secara asynchronous melalui queue.
- Manajemen artikel blog manual lengkap dengan thumbnail, metadata SEO, status publikasi, dan tanggal terbit.
- Endpoint publik untuk konsumsi frontend atau website landing page.

## Tech Stack

- **Backend:** Laravel 12
- **Admin Panel:** Filament 3
- **Auth/API:** Laravel Sanctum
- **Queue:** Database queue
- **Frontend tooling:** Vite + Tailwind CSS 4
- **AI Text Generation:** Google Gemini
- **AI Image Generation:** Google Gemini Image

## Entitas Inti

### `routes`
Menyimpan data rute pengiriman dan tarif.

Kolom penting:
- `wilayah_tujuan`
- `kota_asal`
- `kota_tujuan`
- `harga_per_kg`
- `min_charge_kg`
- `estimasi_hari`
- `slug`
- `image_path`

### `artikel_rutes`
Menyimpan artikel hasil generate AI untuk rute tertentu.

Kolom penting:
- `rute_id`
- `judul`
- `slug`
- `paragraf_pembuka`
- `teks_layanan`
- `teks_tips`
- `teks_faq`
- `status_generate`

### `artikel_blogs`
Menyimpan artikel blog manual.

Kolom penting:
- `judul`
- `slug`
- `thumbnail`
- `konten`
- `meta_title`
- `meta_description`
- `status`
- `published_at`

### `settings`
Menyimpan konfigurasi sederhana berbasis key-value, misalnya prompt permanen untuk generator gambar.

## Fitur Admin

Panel admin berjalan di path:

```bash
/admin
```

Menu utama yang tersedia saat ini:

- **Kelola Rute & Tarif**
  - CRUD data rute
  - import CSV harga
- **Artikel Blog (Manual)**
  - tulis artikel manual
  - upload thumbnail
  - atur meta SEO dan status publish
- **Artikel AI (Generate)**
  - lihat riwayat artikel hasil generate
  - edit hasil AI secara manual
  - filter berdasarkan status generate
- **AI Tools**
  - generator artikel AI berbasis filter rute
  - generator gambar AI untuk thumbnail atau visual rute

## Endpoint API Publik

### 1) Cek ongkir
```http
GET /api/cek-ongkir?asal=Surabaya&tujuan=Makassar
```

Contoh respons sukses:

```json
{
  "status": "sukses",
  "data": {
    "id": 1,
    "wilayah_tujuan": "sulawesi",
    "kota_asal": "Surabaya",
    "kota_tujuan": "Makassar",
    "harga_per_kg": 2800,
    "min_charge_kg": 50,
    "estimasi_hari": "3-4 Hari",
    "slug": "surabaya-makassar"
  }
}
```

### 2) Detail rute berdasarkan slug artikel
```http
GET /api/rute/{slug}
```

Endpoint ini mengembalikan:
- data rute
- artikel AI yang terkait
- artikel terkait lain di wilayah tujuan yang sama

### 3) Featured routes
```http
GET /api/featured-routes
```

Digunakan untuk mengambil daftar rute unggulan yang sudah memiliki gambar.

### 4) Semua artikel
```http
GET /api/semua-artikel
```

Menggabungkan:
- artikel manual dari `artikel_blogs`
- artikel AI dari `artikel_rutes`

Hasilnya diurutkan berdasarkan `created_at` terbaru.

## AI Workflow

### Generator Artikel AI
Fitur ini berjalan dengan alur berikut:

1. Admin memilih filter rute.
2. Admin menentukan jumlah artikel batch.
3. Admin menentukan format judul dan slug menggunakan placeholder:
   - `[asal]`
   - `[tujuan]`
4. Sistem membuat record `artikel_rutes` dengan status `pending`.
5. Job queue memproses generate artikel melalui Gemini.
6. Hasil disimpan ke database dan status berubah menjadi `completed` atau `failed`.

### Generator Gambar AI
Fitur ini berjalan dengan alur berikut:

1. Admin memilih satu atau lebih rute.
2. Admin menyimpan atau memakai prompt permanen.
3. Sistem melempar job ke queue.
4. Job memanggil Gemini Image API.
5. File gambar disimpan ke `storage/app/public/gallery`.
6. Path final disimpan ke kolom `routes.image_path`.

## Kebutuhan Sistem

- PHP **8.2+**
- Composer
- Node.js + npm
- SQLite / MySQL
- Ekstensi PHP standar untuk Laravel

## Setup Lokal

### 1) Clone repository
```bash
git clone https://github.com/trenaldy-online/kargokita-backend.git
cd kargokita-backend
```

### 2) Install dependency backend dan frontend
```bash
composer install
npm install
```

### 3) Siapkan file environment
```bash
cp .env.example .env
```

Untuk quick start lokal, kamu bisa memakai konfigurasi berikut:

```env
APP_NAME="KargoKita Backend"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
SESSION_DRIVER=file
QUEUE_CONNECTION=database
CACHE_STORE=database

GEMINI_API_KEY=your_gemini_api_key_here
```

Lalu buat file SQLite jika perlu:

```bash
touch database/database.sqlite
```

### 4) Generate application key
```bash
php artisan key:generate
```

### 5) Jalankan migration
```bash
php artisan migrate
```

### 6) Buat symbolic link storage
```bash
php artisan storage:link
```

### 7) Buat user admin Filament
```bash
php artisan make:filament-user
```

### 8) Jalankan aplikasi
Pilihan paling praktis:

```bash
composer run dev
```

Perintah ini akan menjalankan:
- Laravel development server
- queue listener
- log watcher (`pail`)
- Vite dev server

Kalau ingin manual:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

## Import CSV Rute

Import rute tersedia dari menu **Kelola Rute & Tarif** di panel admin.

Kolom CSV yang dipakai importer saat ini:

```csv
kota_asal,kota_tujuan,wilayah_tujuan,harga_per_kg,min_charge_kg,estimasi_hari
Surabaya,Makassar,sulawesi,2800,50,4
Surabaya,Kendari,sulawesi,3200,50,5
```

> Catatan: validasi importer saat ini memperlakukan `estimasi_hari` sebagai angka. Jika kamu ingin format tampilan seperti `3-4 Hari`, sesuaikan rule importer atau lakukan pengisian lewat form admin.

## Struktur Folder Singkat

```bash
app/
├── Filament/
│   ├── Imports/
│   ├── Pages/
│   └── Resources/
├── Http/Controllers/
├── Jobs/
└── Models/

database/
├── migrations/
└── seeders/

routes/
├── api.php
└── web.php
```

## Perintah Penting

```bash
# Menjalankan test
composer test

# Menjalankan formatter kode
./vendor/bin/pint

# Menjalankan queue worker terpisah
php artisan queue:listen --tries=1 --timeout=0
```

## Catatan Pengembangan

- Fitur AI **wajib** menjalankan queue worker karena artikel dan gambar diproses di background job.
- Jangan lupa menambahkan `GEMINI_API_KEY` di `.env`, karena variabel ini belum tersedia di file `.env.example` bawaan repo.
- Untuk local development, `SESSION_DRIVER=file` lebih aman dipakai jika kamu belum menambahkan migration untuk tabel session.
- Jika gambar hasil generate atau upload tidak tampil, pastikan perintah `php artisan storage:link` sudah dijalankan.

## Roadmap yang Disarankan

- Tambahkan `README` frontend dan arsitektur integrasi dengan website utama.
- Tambahkan seeders untuk data contoh rute.
- Tambahkan dokumentasi autentikasi dan role admin.
- Tambahkan test untuk endpoint API utama.
- Samakan format `estimasi_hari` antara form, migration, dan importer CSV.

## Lisensi

Project ini mengikuti lisensi **MIT** mengikuti konfigurasi package Laravel di repository.
