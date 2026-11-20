<?php

declare(strict_types=1);

namespace App;

use Throwable;

function routes(): array
{
    return [
        // ----- Halaman publik (10 halaman) -----
        ['GET', '/', 'App\Pub\home'],
        ['GET', '/profil', 'App\Pub\profile'],
        ['GET', '/informasi', 'App\Pub\information'],
        ['GET', '/galeri', 'App\Pub\gallery'],
        ['POST', '/galeri', 'App\Pub\gallery_submit'],
        ['GET', '/jelajah', 'App\Pub\explore'],
        ['GET', '/berita', 'App\Pub\news'],
        ['GET', '/lapor', 'App\Pub\report_form'],
        ['POST', '/lapor', 'App\Pub\report_submit'],
        ['GET', '/cek-status', 'App\Pub\report_status'],
        ['GET', '/kontak', 'App\Pub\contact'],
        ['POST', '/kontak', 'App\Pub\contact_submit'],
        ['GET', '/masuk', 'App\Pub\login_form'],
        ['POST', '/masuk', 'App\Pub\login_submit'],
        ['POST', '/daftar', 'App\Pub\register_submit'],
        ['POST', '/keluar', 'App\Pub\logout'],

        // ----- Data JSON untuk modal / peta -----
        ['GET', '/api/berita/(\d+)', 'App\Pub\api_news'],
        ['GET', '/api/jelajah/(\d+)', 'App\Pub\api_place'],
        ['GET', '/api/peta', 'App\Pub\api_map'],

        // ----- Gambar dari database -----
        ['GET', '/media/(\d+)', 'App\Pub\media'],

        // ----- Instalasi database -----
        ['GET', '/setup', 'App\Setup\page'],
        ['POST', '/setup', 'App\Setup\run'],

        // ----- CMS Admin -----
        ['GET', '/admin', 'App\Admin\dashboard'],
        ['GET', '/admin/pengaturan', 'App\Admin\settings_form'],
        ['POST', '/admin/pengaturan', 'App\Admin\settings_save'],
        ['GET', '/admin/profil', 'App\Admin\profile_form'],
        ['POST', '/admin/profil', 'App\Admin\profile_save'],
        ['GET', '/admin/statistik', 'App\Admin\stats_index'],
        ['GET', '/admin/statistik/(\d+)', 'App\Admin\stats_edit'],
        ['POST', '/admin/statistik/(\d+)', 'App\Admin\stats_save'],
        ['GET', '/admin/laporan', 'App\Admin\reports_index'],
        ['GET', '/admin/laporan/(\d+)', 'App\Admin\report_show'],
        ['POST', '/admin/laporan/(\d+)', 'App\Admin\report_update'],
        ['POST', '/admin/galeri/(\d+)/status', 'App\Admin\gallery_status'],
        ['GET', '/admin/pesan', 'App\Admin\messages_index'],
        ['POST', '/admin/pesan/(\d+)', 'App\Admin\message_action'],
        ['GET', '/admin/pengguna', 'App\Admin\users_index'],
        ['POST', '/admin/pengguna/(\d+)', 'App\Admin\user_action'],

        // CRUD generik: berita, jelajah, galeri, perangkat, jam, wilayah, titik, kategori, seksi, grup
        ['GET', '/admin/kelola/([a-z-]+)', 'App\Crud\index'],
        ['GET', '/admin/kelola/([a-z-]+)/baru', 'App\Crud\form'],
        ['POST', '/admin/kelola/([a-z-]+)/baru', 'App\Crud\save'],
        ['GET', '/admin/kelola/([a-z-]+)/(\d+)', 'App\Crud\form'],
        ['POST', '/admin/kelola/([a-z-]+)/(\d+)', 'App\Crud\save'],
        ['POST', '/admin/kelola/([a-z-]+)/(\d+)/hapus', 'App\Crud\delete'],
    ];
}

function run(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = current_path();

    // Halaman yang tidak butuh session (gambar & setup awal)
    $stateless = str_starts_with($path, '/media/') || $path === '/setup';

    try {
        if (!$stateless) {
            start_session();
            if ($method === 'POST') {
                verify_csrf();
            }
        }

        foreach (routes() as [$m, $pattern, $handler]) {
            if ($m !== $method && !($m === 'GET' && $method === 'HEAD')) {
                continue;
            }
            if (preg_match('#^' . $pattern . '$#', $path, $match)) {
                array_shift($match);
                $handler(...$match);
                return;
            }
        }
        abort(404);
    } catch (\PDOException $e) {
        error_log('[DB] ' . $e->getMessage());
        http_response_code(500);
        $msg = 'Database belum terhubung atau belum diinstal.';
        if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Base table')) {
            $msg = 'Tabel database belum dibuat. Buka /setup untuk menginstal database.';
        }
        echo render('pages/fatal', ['message' => $msg]);
    } catch (Throwable $e) {
        error_log('[APP] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        if (env('APP_DEBUG') === 'true') {
            throw $e;
        }
        abort(500);
    }
}
