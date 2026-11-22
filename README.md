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
vercel.json          ← konfigurasi Vercel Container runtime
Dockerfile.vercel    ← image FrankenPHP untuk Vercel
Caddyfile            ← web server container dan routing front controller
```

## Kenapa ada penyesuaian khusus Vercel?
Vercel menjalankan PHP sebagai Container Function melalui FrankenPHP. Container tidak memiliki disk permanen dan Vercel tidak menyediakan MySQL di dalam aplikasi. Karena itu:
- **Session login disimpan di tabel `sessions`** (bukan file).
- **Foto disimpan di tabel `media`** (bukan folder `uploads/`), lalu ditampilkan lewat `/media/{id}` dengan cache.
- **Database MySQL di-hosting terpisah** dan dihubungkan lewat Environment Variables.

Penyimpanan BLOB pada database eksternal adalah storage permanen yang digunakan saat ini. Filesystem container hanya untuk runtime; jangan menyimpan upload ke folder lokal.

Untuk database hasil migrasi legacy, lihat [database/migrations/README.md](database/migrations/README.md). Jangan menjalankan `/setup` pada database lama.

---

## Deploy ke Vercel

Ikuti [DEPLOYMENT.md](DEPLOYMENT.md). Dokumen tersebut membedakan deployment baru dengan database lama hasil migrasi dan melarang penggunaan `/setup` pada database lama.

---

## Menjalankan di komputer sendiri
Butuh PHP 8.2+ (ekstensi `pdo_mysql`, `fileinfo`) dan MySQL 8 / MariaDB 10.6+.

```bash
cp .env.example .env        # isi koneksi database lokal
php -S localhost:8000 api/index.php
```

Untuk database lokal kosong, jalankan `php database/install.php ADMIN_EMAIL ADMIN_PASSWORD` setelah memastikan backup dan target database benar. Jangan menjalankan installer atau `database/schema.sql` terhadap database lama.
Buka http://localhost:8000

---

## Lisensi pihak ketiga
Chart.js (MIT), Leaflet (BSD-2), ikon Lucide (ISC), ikon merek Simple Icons (CC0), font Inter (OFL), peta © kontributor OpenStreetMap.
