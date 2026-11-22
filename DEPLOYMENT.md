# EcoVillage deployment

Dokumen ini menyiapkan deployment preview tanpa menjalankan migrasi atau mengubah database.

## Runtime

Proyek menggunakan `Dockerfile.vercel` dengan FrankenPHP/PHP 8.4 dan `Caddyfile`. Dokumentasi resmi Vercel (7 Juli 2026) menyatakan bahwa `Dockerfile.vercel` dapat memilih Container preset dan container harus membuka HTTP server pada `$PORT` (default 80). Container Runtime masih berstatus **Beta**, sehingga target aman repository ini adalah Preview sampai build dan smoke test nyata berhasil.

Container hanya menjalankan HTTP request. File lokal tidak dianggap penyimpanan permanen.

Vercel tidak menyediakan runtime PHP native resmi. Image OCI ini dipilih untuk mempertahankan PHP dan ekstensi `pdo_mysql`; FrankenPHP/Caddy berjalan di dalam image, bukan sebagai runtime Vercel native.

## Environment Variables

Atur variable berikut di Vercel untuk Preview dan Production sesuai kebutuhan:

- Gunakan `DATABASE_URL` bila provider memberi connection URL; jika tidak, gunakan `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, dan `DB_PASS`.
- `DB_SSL=true` untuk koneksi TLS; `DB_SSL_CA` wajib diisi dengan path file CA atau isi sertifikat PEM yang valid. Aplikasi akan gagal tertutup jika CA tidak tersedia, tidak dapat dibaca, atau tidak valid; jangan mengosongkan CA dan jangan memakai fallback tanpa verifikasi sertifikat.
- `SETUP_KEY` hanya bila instalasi baru benar-benar diperlukan. Untuk database lama atau database hasil migrasi, jangan membuka `/setup`.
- `APP_DEBUG=false`
- `ADMIN_REGISTER_CODE` hanya sebagai fallback bila kode pendaftaran admin tidak disimpan pada pengaturan database.

Jangan memasukkan `.env`, password, dump database, atau token ke Git maupun image container. `.env.example` hanya template.

Koneksi database harus berasal dari provider yang dapat diakses dari Vercel. XAMPP/MySQL pada `127.0.0.1` tidak dapat digunakan oleh deployment cloud.

## Database dan migrasi

`database/schema.sql` adalah schema instalasi baru dan memiliki `DROP TABLE IF EXISTS`; jangan menjalankannya terhadap database lama. Endpoint `/setup` juga menolak database yang sudah memiliki tabel saat ini maupun tabel legacy yang dikenal. Untuk database lama, gunakan backup yang sudah dibuat, salinan database staging, lalu skrip migrasi terpisah setelah pemetaan kolom, status, ID, dan foto disetujui. Image deployment hanya membawa `schema.sql`, `seed.php`, dan `seed-media`; dump SQL serta skrip migrasi tidak dikemas ke image.

Urutan aman:

1. Pastikan backup dapat direstore ke database staging.
2. Jalankan pemeriksaan struktur dan jumlah data hanya pada staging.
3. Buat mapping `admins` ke `users`, pertahankan hash password yang kompatibel dengan `password_verify()`.
4. Pertahankan ID laporan dan nomor tiket; petakan status lama secara eksplisit. Status `Diteruskan` memerlukan keputusan bisnis sebelum dipetakan.
5. Pindahkan foto ke `media` hanya setelah file fisiknya ditemukan dan checksum/integritasnya dicatat.
6. Validasi jumlah baris, foreign key, nomor tiket unik, dan riwayat status sebelum cutover.
7. Uji login admin, laporan, cek status, galeri, berita, kontak, dan endpoint media pada staging.

Migrasi tidak dijalankan oleh deployment dan tidak dilakukan melalui `/setup`.

Untuk instalasi database baru melalui CLI, password admin harus diberikan eksplisit dan tidak memiliki fallback:

```text
php database/install.php ADMIN_EMAIL ADMIN_PASSWORD
```

Seed warga hanya membuat hash acak untuk fixture; tidak ada password demo yang disebarkan di source atau UI.

## Media dan session

Aplikasi saat ini menyimpan media dan session di database eksternal melalui tabel `media` dan `sessions`. Ini adalah solusi permanen untuk Preview: filesystem container Vercel tidak dipakai untuk data aplikasi, sedangkan BLOB MySQL tetap tersedia setelah restart atau scale-out.

Upload yang diterima aplikasi hanya JPG, PNG, dan WEBP dengan batas 3 MB. Pastikan provider database mengizinkan BLOB dan koneksi TLS. Object storage belum diperlukan untuk konfigurasi sekarang; jika volume media melebihi batas atau biaya database, buat adapter object storage dan migrasi media terpisah sebelum mengganti perilaku aplikasi. Jangan menghapus path foto legacy yang belum ditemukan.

## Mengganti email admin hasil migrasi

Karena tabel legacy hanya memiliki `username`, migrasi membuat email sementara `satya@legacy.invalid`. Hash password tidak diubah. Setelah target staging dipastikan benar, admin atau operator database dapat mengganti email dengan alamat sebenarnya:

```sql
UPDATE users
SET email = 'alamat-sebenarnya@example.com'
WHERE id = 1 AND role = 'admin' AND email = 'satya@legacy.invalid';
```

Pastikan email belum dipakai akun lain, lalu uji login. Jangan menjalankan `password_hash`, reset password, atau menghapus akun lama.

## Backup dan verifikasi backup

Sebelum cutover, gunakan akun database yang memiliki hak dump dan simpan backup di luar repository:

```text
mysqldump --single-transaction --routines --triggers --hex-blob -h DB_HOST -P DB_PORT -u DB_USER -p DB_NAME > ecovillage-before-cutover.sql
```

Verifikasi bahwa dump tidak kosong, dapat dibaca, dan dapat direstore ke database disposable/staging. Bandingkan jumlah tabel, users admin, reports, dan report_updates setelah restore. Simpan checksum file backup dan jangan menaruh file dump di Git. Rollback dilakukan dengan mengarahkan aplikasi kembali ke database snapshot sebelum cutover atau memulihkan snapshot ke database baru; tidak memakai SQL reverse yang menghapus data.

## Verifikasi lokal

Dengan Docker aktif:

```text
vercel dev --local
```

Uji minimal pada URL yang diberikan Vercel CLI:

- `GET /` — halaman utama melalui front controller.
- `GET /assets/...` — asset statis.
- `GET /lapor` dan `POST /lapor` — form/laporan dengan foto wajib.
- `GET /cek-status?ticket_number=...` dan `?tiket=...` — riwayat tiket.
- `GET /masuk` dan `POST /masuk` — autentikasi.
- `/src/...`, `/database/...`, `/api/...`, dan `/.env` — harus 404.

Catatan routing: `/api/berita/...`, `/api/jelajah/...`, dan `/api/peta` adalah route aplikasi yang harus tetap aktif. Yang diblokir hanya `/api/index.php` dan `/api/php.ini` agar source/config tidak tersaji sebagai file publik.

Sebelum preview, lint seluruh PHP dengan PHP 8.2+:

```text
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & C:\xampp\php\php.exe -l $_.FullName }
```

Preview hanya boleh dipromosikan setelah env, database staging, log runtime, dan seluruh alur di atas berhasil diuji. Tidak ada perubahan production otomatis dari konfigurasi ini.

## Status audit terakhir

- PHP lint dan `vercel.json`: lulus.
- Migrasi staging: 7/7 laporan, 9/9 riwayat, 1/1 admin, rerun tanpa duplikasi.
- Smoke test staging: halaman publik, login form, redirect admin, profil, cek status, dan API peta lulus.
- Docker build lokal: lulus; Caddy/FrankenPHP start dan asset statis merespons `200`.
- Preview Vercel belum dibuat karena Vercel CLI dan akses akun belum tersedia pada workspace ini.
