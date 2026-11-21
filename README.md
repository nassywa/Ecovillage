# EcoVillage — Website Desa Marga Mulya

Website desa dengan CMS untuk **Desa Marga Mulya, Kec. Pangkalan Banteng, Kab. Kotawaringin Barat, Kalimantan Tengah**.
Dibuat dengan **HTML, CSS, JavaScript, PHP 8.3, dan MySQL**, lalu di-deploy ke **Vercel**.

> Seluruh angka dan nama pada data contoh bersifat fiktif (dummy) sesuai struktur data EcoQuest IIT Challenge 2026.

---

## Fitur

### 10 halaman publik
| # | Halaman | URL | Isi |
|---|---------|-----|-----|
| 1 | Beranda | `/` | Hero hijau, kartu Penduduk/RT-RW/Pelayanan, teks berjalan, Layanan Cepat (LaporDesa menonjol, Cek Status, Jelajah Desa, Berita), Berita Terbaru, jam kerja di footer |
| 2 | Profil Desa | `/profil` | Foto gerbang, tentang, sejarah, visi & misi, data demografi, struktur pemerintahan, perangkat desa, jam pelayanan, **peta interaktif Desa/RW/RT** (zoom, layar penuh, cari Kantor Desa, lokasi saat ini), luas & batas wilayah, grafik penggunaan lahan |
| 3 | Informasi Desa | `/informasi` | 8 kartu kategori **A–H** (jumlah set data otomatis); klik untuk membuka grafik (Chart.js) + tabel rincian angka |
| 4 | Galeri | `/galeri` | Masonry, filter kategori, lightbox, "Muat lebih banyak", **warga bisa kirim foto → perlu persetujuan admin** |
| 5 | Sudut Istimewa (Jelajah Desa) | `/jelajah` | Sudut Favorit Warga, Produk Unggulan, direktori UMKM/Wisata/Budaya dengan pencarian, filter, paginasi, detail + tombol WhatsApp |
| 6 | Berita & Kegiatan | `/berita` | Berita utama, filter Semua/Berita/Kegiatan/Organisasi, pencarian, paginasi, detail berita |
| 7 | Lapor Desa | `/lapor` | Wajib masuk akun; 4 langkah (kategori, lokasi + titik peta, uraian, foto), nomor tiket otomatis |
| 8 | Cek Status | `/cek-status` | Cari nomor tiket, filter Semua/kategori/status, progres Diterima → Diproses → Selesai, riwayat |
| 9 | Kontak | `/kontak` | Alamat, email, telepon, WhatsApp, peta Kantor Desa, jam pelayanan, Facebook/TikTok/YouTube/Instagram, form kirim pesan |
| 10 | Masuk / Daftar | `/masuk` | Masuk & daftar sebagai **Warga** atau **Admin** (admin butuh kode pendaftaran) |

### CMS Admin (`/admin`, tidak dihitung dalam batas 10 halaman)
Dasbor statistik, Laporan Warga (ubah status + catatan), Pesan Masuk, Berita & Kegiatan, Jelajah Desa, Galeri (setujui/tolak foto warga),
Profil & Geografis, Data Informasi A–H (edit angka setiap grafik), Perangkat Desa, Peta Wilayah (gambar batas dengan klik peta),
Titik Penting, Beranda & Kontak (teks, foto, angka, sosial media), Jam Pelayanan, Kategori Laporan, Pengguna.

**Semua konten publik diambil dari database dan dikelola lewat CMS — tidak ada data desa yang di-hardcode.**

### Lainnya
Responsif (mobile-first, navigasi bawah di HP), mode gelap, CSRF protection, password hash, honeypot anti-spam, foto otomatis diperkecil di browser sebelum diunggah.

---

## Struktur folder

```
api/index.php        ← front controller (satu-satunya fungsi PHP di Vercel)
api/php.ini          ← konfigurasi PHP runtime
src/                 ← kode aplikasi (router, controller, CRUD, view)
assets/              ← CSS, JS, font, ikon, library (Chart.js, Leaflet)
database/schema.sql  ← struktur tabel MySQL
database/seed.php    ← data contoh Desa Marga Mulya
database/ecovillage.sql ← dump MySQL siap impor (skema + data contoh)
vercel.json          ← konfigurasi Vercel + runtime vercel-php (PHP 8.3)
```

## Kenapa ada penyesuaian khusus Vercel?
Vercel menjalankan PHP sebagai *serverless function* (runtime komunitas `vercel-php@0.7.4`, PHP 8.3) yang **tidak punya disk tetap** dan **tidak menyediakan MySQL**. Karena itu:
- **Session login disimpan di tabel `sessions`** (bukan file).
- **Foto disimpan di tabel `media`** (bukan folder `uploads/`), lalu ditampilkan lewat `/media/{id}` dengan cache.
- **Database MySQL di-hosting terpisah** dan dihubungkan lewat Environment Variables.

---

## Deploy ke Vercel (langkah demi langkah)

### 1. Siapkan database MySQL online (gratis: Clever Cloud MySQL DEV)
1. Login ke https://console.clever-cloud.com → **Create** → **an add-on** → **MySQL** → pilih paket **DEV** (gratis).
2. Buka add-on tersebut → tab **Information / Environment variables**, catat `MYSQL_ADDON_HOST`, `MYSQL_ADDON_PORT`, `MYSQL_ADDON_DB`, `MYSQL_ADDON_USER`, `MYSQL_ADDON_PASSWORD`.
3. Isi nilai tersebut ke `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` di Vercel. `DB_SSL` biarkan `false`.

> Paket DEV gratis berkapasitas kecil (sekitar 10 MB) dan khusus testing. Foto otomatis dikompres di browser dan session kosong tidak disimpan supaya hemat. Untuk produksi, gunakan paket MySQL berbayar.

### 2. Upload kode ke GitHub
Buat repository baru, lalu upload seluruh isi folder ini (file `.env` jangan ikut).

### 3. Import project di Vercel
1. Buka https://vercel.com/new → pilih repository.
2. **Framework Preset: Other**. Build Command & Output Directory dikosongkan.
3. Buka **Environment Variables**, isi:

| Nama | Contoh |
|------|--------|
| `DB_HOST` | `mysql-xxxx.example.com` |
| `DB_PORT` | `3306` |
| `DB_NAME` | `ecovillage` |
| `DB_USER` | `ecouser` |
| `DB_PASS` | `********` |
| `DB_SSL` | `true` (untuk MySQL cloud) |
| `SETUP_KEY` | teks acak, mis. `mm-setup-8f3k2` |

4. Klik **Deploy**.

### 4. Instal database
Pilih salah satu:
- **Lewat website:** buka `https://NAMA-PROJECT.vercel.app/setup`, isi `SETUP_KEY`, email dan kata sandi admin → **Instal database**.
- **Lewat file SQL:** impor `database/ecovillage.sql` ke database (DBeaver / MySQL Workbench / `mysql < database/ecovillage.sql`). Admin default: `admin@margamulya.id` / `admin12345` — **segera ganti** dengan mendaftar admin baru lalu hapus akun default.

Setelah instalasi, **hapus atau ganti `SETUP_KEY`** di Vercel lalu redeploy.

### 5. Selesai
- Website: `https://NAMA-PROJECT.vercel.app`
- CMS: `https://NAMA-PROJECT.vercel.app/admin`
- Akun demo warga: `warga@margamulya.id` / `warga12345`
- Kode daftar admin (bisa diubah di CMS → Beranda & Kontak → Keamanan): `MARGAMULYA-ADMIN-2026`

---

## Menjalankan di komputer sendiri
Butuh PHP 8.2+ (ekstensi `pdo_mysql`, `fileinfo`) dan MySQL 8 / MariaDB 10.6+.

```bash
cp .env.example .env        # isi koneksi database lokal
php database/install.php admin@margamulya.id admin12345
php -S localhost:8000 api/index.php
```
Buka http://localhost:8000

---

## Lisensi pihak ketiga
Chart.js (MIT), Leaflet (BSD-2), ikon Lucide (ISC), ikon merek Simple Icons (CC0), font Inter (OFL), peta © kontributor OpenStreetMap.
