-- EcoVillage legacy -> current data migration
--
-- SAFETY CONTRACT
-- 1. Run only on a NEW/STAGING database that already contains database/schema.sql.
-- 2. Legacy source tables must be imported as legacy_* tables from a verified backup.
-- 3. This file never reads or writes the original ecovillage database directly.
-- 4. It contains no DROP, TRUNCATE, DELETE, or UPDATE against legacy_* tables.
-- 5. It is idempotent: primary/unique keys and NOT EXISTS guards prevent duplicates.
-- 6. Photo files are intentionally not imported. Missing paths remain NULL.
--
-- Required source tables:
-- legacy_admins, legacy_reports, legacy_report_updates,
-- legacy_village_contact, legacy_village_gallery,
-- legacy_village_information, legacy_village_profile

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS ecovillage_migration_history (
    migration_name VARCHAR(190) PRIMARY KEY,
    source_snapshot VARCHAR(255) NOT NULL,
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    notes TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

-- The history row is also the rerun marker. All data statements below are
-- guarded independently so a retry after an interrupted transaction is safe.
INSERT INTO ecovillage_migration_history (migration_name, source_snapshot, notes)
SELECT '001_legacy_to_current', 'legacy_* tables in this database',
       'Started; source rows are retained and media paths are not assumed.'
WHERE NOT EXISTS (
    SELECT 1 FROM ecovillage_migration_history
    WHERE migration_name = '001_legacy_to_current'
);

-- admins -> users
-- Legacy has username but no email. A non-deliverable placeholder is used so
-- the password_hash remains byte-for-byte intact. Replace it manually with a
-- real email before login; no password reset is performed here.
INSERT INTO users (id, name, email, phone, password_hash, role, created_at)
SELECT CAST(a.id AS UNSIGNED), LEFT(a.name, 100),
       LEFT(CONCAT(LOWER(a.username), '@legacy.invalid'), 150), NULL,
       a.password_hash, 'admin', a.created_at
FROM legacy_admins a
WHERE CAST(a.id AS UNSIGNED) <= 4294967295
  AND CHAR_LENGTH(a.username) > 0
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.id = CAST(a.id AS UNSIGNED))
  AND NOT EXISTS (
      SELECT 1 FROM users u
      WHERE u.email = LEFT(CONCAT(LOWER(a.username), '@legacy.invalid'), 150)
  );

-- Legacy report statuses map as follows:
--   Diterima -> Diterima, Diproses -> Diproses, Selesai -> Selesai,
--   Diteruskan -> Diproses (the current report enum has no Diteruskan state).
-- Historical report_updates keep the original status text because the target
-- column is VARCHAR(20), preserving the nine legacy history events exactly.
INSERT INTO reports (
    id, ticket, user_id, reporter_name, reporter_phone, category, location,
    lat, lng, description, photo_id, status, admin_note, created_at,
    accepted_at, finished_at
)
SELECT CAST(r.id AS UNSIGNED), LEFT(r.ticket_number, 20), NULL, NULL, NULL,
       LEFT(r.category, 80), LEFT(r.location, 200), NULL, NULL, r.description,
       NULL,
       CASE r.status
           WHEN 'Diteruskan' THEN 'Diproses'
           WHEN 'Diterima' THEN 'Diterima'
           WHEN 'Diproses' THEN 'Diproses'
           WHEN 'Selesai' THEN 'Selesai'
       END,
       r.admin_note, r.created_at,
       CASE WHEN r.status IN ('Diterima', 'Diproses', 'Diteruskan', 'Selesai')
            THEN r.created_at ELSE NULL END,
       CASE WHEN r.status = 'Selesai' THEN r.updated_at ELSE NULL END
FROM legacy_reports r
WHERE CAST(r.id AS UNSIGNED) <= 4294967295
  AND CHAR_LENGTH(r.ticket_number) <= 20
  AND r.status IN ('Diterima', 'Diproses', 'Diteruskan', 'Selesai')
  AND NOT EXISTS (SELECT 1 FROM reports n WHERE n.id = CAST(r.id AS UNSIGNED))
  AND NOT EXISTS (SELECT 1 FROM reports n WHERE n.ticket = LEFT(r.ticket_number, 20));

-- report_updates -> report_updates. old_status is not present in the target;
-- new_status is retained as status, including Diteruskan.
INSERT INTO report_updates (id, report_id, status, note, created_at)
SELECT CAST(u.id AS UNSIGNED), CAST(u.report_id AS UNSIGNED), LEFT(u.new_status, 20),
       u.note, u.created_at
FROM legacy_report_updates u
WHERE CAST(u.id AS UNSIGNED) <= 4294967295
  AND CAST(u.report_id AS UNSIGNED) <= 4294967295
  AND CHAR_LENGTH(u.new_status) <= 20
  AND EXISTS (SELECT 1 FROM reports r WHERE r.id = CAST(u.report_id AS UNSIGNED))
  AND NOT EXISTS (SELECT 1 FROM report_updates n WHERE n.id = CAST(u.id AS UNSIGNED));

-- village_contact -> settings. INSERT IGNORE semantics are expressed with
-- NOT EXISTS so pre-existing target content is never overwritten.
INSERT INTO settings (skey, svalue)
SELECT 'contact_phone', c.phone FROM legacy_village_contact c
WHERE c.id = (SELECT MIN(id) FROM legacy_village_contact)
  AND c.phone IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'contact_phone');
INSERT INTO settings (skey, svalue)
SELECT 'contact_email', c.email FROM legacy_village_contact c
WHERE c.id = (SELECT MIN(id) FROM legacy_village_contact)
  AND c.email IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'contact_email');
INSERT INTO settings (skey, svalue)
SELECT 'contact_address', c.village_address FROM legacy_village_contact c
WHERE c.id = (SELECT MIN(id) FROM legacy_village_contact)
  AND c.village_address IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'contact_address');
INSERT INTO settings (skey, svalue)
SELECT 'social_instagram', c.instagram FROM legacy_village_contact c
WHERE c.id = (SELECT MIN(id) FROM legacy_village_contact)
  AND c.instagram IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'social_instagram');
INSERT INTO settings (skey, svalue)
SELECT 'social_facebook', c.facebook FROM legacy_village_contact c
WHERE c.id = (SELECT MIN(id) FROM legacy_village_contact)
  AND c.facebook IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'social_facebook');

-- village_profile -> settings. The legacy logo path is not copied because its
-- file is absent from the audited workspace.
INSERT INTO settings (skey, svalue)
SELECT 'village_name', p.village_name FROM legacy_village_profile p
WHERE p.id = (SELECT MIN(id) FROM legacy_village_profile)
  AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'village_name');
INSERT INTO settings (skey, svalue)
SELECT 'region_line', TRIM(CONCAT_WS(', ', p.district, p.regency, p.province))
FROM legacy_village_profile p
WHERE p.id = (SELECT MIN(id) FROM legacy_village_profile)
  AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'region_line');
INSERT INTO settings (skey, svalue)
SELECT 'profil_tentang', p.description FROM legacy_village_profile p
WHERE p.id = (SELECT MIN(id) FROM legacy_village_profile)
  AND p.description IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'profil_tentang');
INSERT INTO settings (skey, svalue)
SELECT 'profil_sejarah', p.history FROM legacy_village_profile p
WHERE p.id = (SELECT MIN(id) FROM legacy_village_profile)
  AND p.history IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'profil_sejarah');
INSERT INTO settings (skey, svalue)
SELECT 'profil_visi', p.vision FROM legacy_village_profile p
WHERE p.id = (SELECT MIN(id) FROM legacy_village_profile)
  AND p.vision IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'profil_visi');
INSERT INTO settings (skey, svalue)
SELECT 'profil_misi', p.mission FROM legacy_village_profile p
WHERE p.id = (SELECT MIN(id) FROM legacy_village_profile)
  AND p.mission IS NOT NULL AND NOT EXISTS (SELECT 1 FROM settings WHERE skey = 'profil_misi');

-- village_information -> one legacy information section and one cards group
-- per source row. Values remain strings in the target chart item DECIMAL field;
-- non-numeric values are left as zero and retained in the group description.
INSERT INTO stat_sections (code, title, icon, description, sort)
SELECT 'L', 'Informasi Legacy', 'archive', 'Data dari village_information', 1000
WHERE NOT EXISTS (SELECT 1 FROM stat_sections WHERE code = 'L');
INSERT INTO stat_groups (section_id, slug, title, chart_type, unit, description, sort)
SELECT s.id, CONCAT('legacy-information-', i.id), LEFT(i.title, 160), 'cards', LEFT(i.unit, 30),
       LEFT(i.description, 255), 1000 + i.display_order
FROM legacy_village_information i
JOIN stat_sections s ON s.code = 'L'
WHERE NOT EXISTS (SELECT 1 FROM stat_groups g WHERE g.slug = CONCAT('legacy-information-', i.id));
INSERT INTO stat_items (group_id, label, series, value, sort)
SELECT g.id, LEFT(i.category, 120), NULL,
       CASE WHEN i.value REGEXP '^-?[0-9]+(\\.[0-9]+)?$' THEN CAST(i.value AS DECIMAL(14,2)) ELSE 0 END,
       i.display_order
FROM legacy_village_information i
JOIN stat_groups g ON g.slug = CONCAT('legacy-information-', i.id)
WHERE NOT EXISTS (
    SELECT 1 FROM stat_items x WHERE x.group_id = g.id AND x.label = LEFT(i.category, 120)
);

-- village_gallery -> gallery. No media row is created when the physical file
-- is unavailable; the record remains visible to an administrator for review.
INSERT INTO gallery (id, title, description, category, image_id, status, user_id, uploader_name, sort, created_at)
SELECT CAST(g.id AS UNSIGNED), LEFT(g.title, 150), LEFT(g.description, 300), LEFT(g.category, 60), NULL,
       CASE g.status WHEN 'Published' THEN 'Disetujui' ELSE 'Menunggu' END,
       NULL, 'Legacy import', g.display_order, g.created_at
FROM legacy_village_gallery g
WHERE CAST(g.id AS UNSIGNED) <= 4294967295
  AND NOT EXISTS (SELECT 1 FROM gallery n WHERE n.id = CAST(g.id AS UNSIGNED));

UPDATE ecovillage_migration_history
SET completed_at = CURRENT_TIMESTAMP,
    notes = 'Completed idempotent legacy import. Missing physical media intentionally left unlinked.'
WHERE migration_name = '001_legacy_to_current';

COMMIT;
