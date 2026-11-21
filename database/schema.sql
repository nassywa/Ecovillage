-- =========================================================
-- EcoVillage Desa Marga Mulya — skema database MySQL 8 / MariaDB 10.6+
-- =========================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS report_updates, reports, report_categories, messages, gallery, places, news,
    map_points, map_regions, stat_items, stat_groups, stat_sections, officials, office_hours,
    users, sessions, settings, media;

SET FOREIGN_KEY_CHECKS = 1;

-- Foto & gambar (disimpan di database karena disk Vercel bersifat sementara)
CREATE TABLE media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mime VARCHAR(60) NOT NULL,
    size INT UNSIGNED NOT NULL DEFAULT 0,
    original_name VARCHAR(190) NULL,
    data MEDIUMBLOB NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pengaturan situs (teks beranda, kontak, sosial media, profil, dll)
CREATE TABLE settings (
    skey VARCHAR(100) PRIMARY KEY,
    svalue MEDIUMTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Session login (pengganti file session PHP)
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    data MEDIUMTEXT NOT NULL,
    expires_at INT UNSIGNED NOT NULL,
    INDEX idx_sessions_exp (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Akun warga & admin
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
<<<<<<< HEAD
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
-- 4. Tabel profil desa
CREATE TABLE IF NOT EXISTS village_profile (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    village_name VARCHAR(150) NOT NULL,
    district VARCHAR(150) NULL,
    regency VARCHAR(150) NULL,
    province VARCHAR(150) NULL,
    description TEXT NULL,
    history TEXT NULL,
    vision TEXT NULL,
    mission TEXT NULL,
    geographic_info TEXT NULL,
    village_address TEXT NULL,
    village_logo VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
-- 5. Tabel informasi desa
CREATE TABLE IF NOT EXISTS village_information (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    title VARCHAR(200) NOT NULL,
    value VARCHAR(255) NULL,
    unit VARCHAR(50) NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    reference_year YEAR NULL,
    status ENUM('Draft', 'Published') NOT NULL DEFAULT 'Draft',
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
-- 6. Tabel galeri desa
CREATE TABLE IF NOT EXISTS village_gallery (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    category VARCHAR(100) NULL,
    image VARCHAR(255) NOT NULL,
    status ENUM('Draft', 'Published') NOT NULL DEFAULT 'Draft',
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
-- 7. Tabel kontak desa
CREATE TABLE IF NOT EXISTS village_contact (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    village_address TEXT NULL,
    office_hours VARCHAR(150) NULL,
    website VARCHAR(255) NULL,
    instagram VARCHAR(255) NULL,
    facebook VARCHAR(255) NULL,
    map_url TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
=======
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','warga') NOT NULL DEFAULT 'warga',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jam kerja / jam pelayanan kantor desa
CREATE TABLE office_hours (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    day VARCHAR(20) NOT NULL,
    open_time VARCHAR(10) NULL,
    close_time VARCHAR(10) NULL,
    is_closed TINYINT(1) NOT NULL DEFAULT 0,
    sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Perangkat / aparat desa (struktur pemerintahan)
CREATE TABLE officials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    tier TINYINT NOT NULL DEFAULT 3 COMMENT '1=Kepala Desa, 2=Sekretaris, 3=Kasi/Kaur, 4=Kepala Dusun/Ketua RW',
    photo_id INT UNSIGNED NULL,
    sort INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_officials_photo FOREIGN KEY (photo_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data statistik desa (poin A–H) → ditampilkan sebagai grafik
CREATE TABLE stat_sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code CHAR(1) NOT NULL UNIQUE,
    title VARCHAR(120) NOT NULL,
    icon VARCHAR(50) NOT NULL DEFAULT 'chart-column',
    description VARCHAR(255) NULL,
    sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stat_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id INT UNSIGNED NOT NULL,
    slug VARCHAR(80) NULL UNIQUE,
    title VARCHAR(160) NOT NULL,
    chart_type ENUM('bar','hbar','stacked','pie','doughnut','line','cards','table') NOT NULL DEFAULT 'bar',
    unit VARCHAR(30) NULL,
    description VARCHAR(255) NULL,
    sort INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_groups_section FOREIGN KEY (section_id) REFERENCES stat_sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stat_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    label VARCHAR(120) NOT NULL,
    series VARCHAR(80) NULL,
    value DECIMAL(14,2) NOT NULL DEFAULT 0,
    sort INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_items_group FOREIGN KEY (group_id) REFERENCES stat_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Peta wilayah (poligon GeoJSON untuk Desa, RW, RT)
CREATE TABLE map_regions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    level ENUM('desa','rw','rt') NOT NULL,
    name VARCHAR(80) NOT NULL,
    parent_name VARCHAR(80) NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#16803C',
    leader VARCHAR(100) NULL,
    households INT NULL,
    population INT NULL,
    geojson MEDIUMTEXT NOT NULL COMMENT 'Array koordinat [[lat,lng],...]',
    sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Titik penting di peta (kantor desa, sekolah, posyandu, dll)
CREATE TABLE map_points (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    type ENUM('Kantor Desa','Pendidikan','Kesehatan','Fasilitas Umum','Wisata','UMKM') NOT NULL DEFAULT 'Fasilitas Umum',
    lat DECIMAL(10,7) NOT NULL,
    lng DECIMAL(10,7) NOT NULL,
    description VARCHAR(255) NULL,
    sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Berita & kegiatan
CREATE TABLE news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    category ENUM('Berita','Kegiatan','Pengumuman') NOT NULL DEFAULT 'Berita',
    featured TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Berita utama',
    excerpt VARCHAR(300) NULL,
    content MEDIUMTEXT NOT NULL,
    image_id INT UNSIGNED NULL,
    author VARCHAR(100) NULL,
    published_at DATE NOT NULL,
    status ENUM('Terbit','Draft') NOT NULL DEFAULT 'Terbit',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_news_pub (status, published_at),
    CONSTRAINT fk_news_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jelajah Desa: UMKM, Wisata, Budaya
CREATE TABLE places (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category ENUM('UMKM','Wisata','Budaya') NOT NULL,
    subcategory VARCHAR(80) NULL,
    description TEXT NOT NULL,
    location VARCHAR(150) NULL,
    contact VARCHAR(100) NULL,
    price VARCHAR(100) NULL,
    hours VARCHAR(100) NULL,
    image_id INT UNSIGNED NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('Tampil','Sembunyi') NOT NULL DEFAULT 'Tampil',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_places_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Galeri foto (admin & warga; foto warga perlu persetujuan admin)
CREATE TABLE gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description VARCHAR(300) NULL,
    category VARCHAR(60) NULL,
    image_id INT UNSIGNED NULL,
    status ENUM('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
    user_id INT UNSIGNED NULL,
    uploader_name VARCHAR(100) NULL,
    sort INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gallery_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL,
    CONSTRAINT fk_gallery_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LaporDesa
CREATE TABLE report_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    icon VARCHAR(50) NOT NULL DEFAULT 'circle-alert',
    sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket VARCHAR(20) NOT NULL UNIQUE,
    user_id INT UNSIGNED NULL,
    reporter_name VARCHAR(100) NULL,
    reporter_phone VARCHAR(30) NULL,
    category VARCHAR(80) NOT NULL,
    location VARCHAR(200) NOT NULL,
    lat DECIMAL(10,7) NULL,
    lng DECIMAL(10,7) NULL,
    description TEXT NOT NULL,
    photo_id INT UNSIGNED NULL,
    status ENUM('Menunggu','Diterima','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Menunggu',
    admin_note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    accepted_at DATETIME NULL,
    finished_at DATETIME NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reports_status (status),
    CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_reports_photo FOREIGN KEY (photo_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE report_updates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id INT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_updates_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pesan dari halaman Kontak
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> origin/main
