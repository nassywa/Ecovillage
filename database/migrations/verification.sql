-- Read-only verification for a database that contains both legacy_* sources
-- and the current schema after 001_legacy_to_current.sql.

SELECT migration_name, completed_at
FROM ecovillage_migration_history
WHERE migration_name = '001_legacy_to_current';

SELECT 'legacy_admins' AS table_name, COUNT(*) AS row_count FROM legacy_admins
UNION ALL SELECT 'users_admin', COUNT(*) FROM users WHERE role = 'admin'
UNION ALL SELECT 'legacy_reports', COUNT(*) FROM legacy_reports
UNION ALL SELECT 'reports', COUNT(*) FROM reports
UNION ALL SELECT 'legacy_report_updates', COUNT(*) FROM legacy_report_updates
UNION ALL SELECT 'report_updates', COUNT(*) FROM report_updates
UNION ALL SELECT 'legacy_village_gallery', COUNT(*) FROM legacy_village_gallery
UNION ALL SELECT 'gallery', COUNT(*) FROM gallery;

SELECT r.id, r.ticket_number AS legacy_ticket, n.ticket AS current_ticket,
       r.status AS legacy_status, n.status AS current_status
FROM legacy_reports r
LEFT JOIN reports n ON n.id = r.id
ORDER BY r.id;

SELECT COUNT(*) AS orphan_report_updates
FROM report_updates u
LEFT JOIN reports r ON r.id = u.report_id
WHERE r.id IS NULL;

SELECT COUNT(*) AS orphan_report_users
FROM reports r
LEFT JOIN users u ON u.id = r.user_id
WHERE r.user_id IS NOT NULL AND u.id IS NULL;

SELECT COUNT(*) AS orphan_report_media
FROM reports r
LEFT JOIN media m ON m.id = r.photo_id
WHERE r.photo_id IS NOT NULL AND m.id IS NULL;

SELECT COUNT(*) AS migrated_admins_with_legacy_hash
FROM users u
JOIN legacy_admins a ON a.id = u.id
WHERE u.role = 'admin' AND u.password_hash = a.password_hash;

SELECT u.id, u.email, u.name, u.role
FROM users u
JOIN legacy_admins a ON a.id = u.id;
