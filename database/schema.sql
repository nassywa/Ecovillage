
-- =====================================
-- ECOVILLAGE DATABASE SCHEMA
-- =====================================

-- 1. Tabel laporan warga
CREATE TABLE IF NOT EXISTS reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(30) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    rt VARCHAR(10) NULL,
    rw VARCHAR(10) NULL,
    photo_path VARCHAR(255) NULL,
    status ENUM(
        'Diterima',
        'Diproses',
        'Diteruskan',
        'Selesai'
    ) NOT NULL DEFAULT 'Diterima',
    admin_note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- 2. Tabel riwayat laporan
CREATE TABLE IF NOT EXISTS report_updates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_report_updates
        FOREIGN KEY (report_id)
        REFERENCES reports(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- 3. Tabel admin
CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
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
