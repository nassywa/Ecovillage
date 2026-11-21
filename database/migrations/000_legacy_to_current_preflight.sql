-- EcoVillage legacy -> current migration preparation
-- STATUS: PREFLIGHT ONLY. THIS FILE MUST NOT BE TREATED AS A DATA MIGRATION.
--
-- Safety:
--   * This file intentionally contains SELECT statements only.
--   * It does not create, alter, copy, delete, truncate, or rename anything.
--   * Do not run it until the active database connection has been independently verified.
--
-- Required before writing the data migration:
--   1. Export the exact SHOW CREATE TABLE output for all seven legacy tables.
--   2. Provide a list of all legacy photo files and their original paths.
--   3. Confirm the password hash format in admins.password (or its actual column).
--   4. Confirm the legacy report status values and the legacy report_updates key column.
--
-- The application runtime uses DATABASE_URL or DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASS
-- from src/core.php. config/database.php is only a separate legacy test connection.

-- A. Confirm which legacy tables exist in the selected database.
SELECT TABLE_NAME, TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
    'admins',
    'reports',
    'report_updates',
    'village_contact',
    'village_gallery',
    'village_information',
    'village_profile'
  )
ORDER BY TABLE_NAME;

-- B. Capture exact legacy columns without assuming their names or types.
SELECT TABLE_NAME, ORDINAL_POSITION, COLUMN_NAME, COLUMN_TYPE,
       IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
    'admins',
    'reports',
    'report_updates',
    'village_contact',
    'village_gallery',
    'village_information',
    'village_profile'
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;

-- C. Capture existing row counts before any migration.
SELECT 'admins' AS table_name, COUNT(*) AS row_count FROM admins
UNION ALL
SELECT 'reports', COUNT(*) FROM reports
UNION ALL
SELECT 'report_updates', COUNT(*) FROM report_updates
UNION ALL
SELECT 'village_contact', COUNT(*) FROM village_contact
UNION ALL
SELECT 'village_gallery', COUNT(*) FROM village_gallery
UNION ALL
SELECT 'village_information', COUNT(*) FROM village_information
UNION ALL
SELECT 'village_profile', COUNT(*) FROM village_profile;

-- D. Compare the current application tables with the selected database.
SELECT required.table_name,
       CASE WHEN actual.TABLE_NAME IS NULL THEN 'MISSING' ELSE 'PRESENT' END AS state
FROM (
    SELECT 'media' AS table_name
    UNION ALL SELECT 'settings'
    UNION ALL SELECT 'sessions'
    UNION ALL SELECT 'users'
    UNION ALL SELECT 'office_hours'
    UNION ALL SELECT 'officials'
    UNION ALL SELECT 'stat_sections'
    UNION ALL SELECT 'stat_groups'
    UNION ALL SELECT 'stat_items'
    UNION ALL SELECT 'map_regions'
    UNION ALL SELECT 'map_points'
    UNION ALL SELECT 'news'
    UNION ALL SELECT 'places'
    UNION ALL SELECT 'gallery'
    UNION ALL SELECT 'report_categories'
    UNION ALL SELECT 'reports'
    UNION ALL SELECT 'report_updates'
    UNION ALL SELECT 'messages'
) AS required
LEFT JOIN information_schema.TABLES AS actual
  ON actual.TABLE_SCHEMA = DATABASE()
 AND actual.TABLE_NAME = required.table_name
ORDER BY required.table_name;

-- E. After the data migration is designed and reviewed, run equivalent
-- count/integrity checks for the new tables:
--   * admins -> users: account count and admin role count
--   * reports: report count and unique ticket count
--   * report_updates: history count and orphan report_id count
--   * legacy photos -> media: media count and every image reference
--   * village_profile/contact/information/gallery -> settings/stat_*/gallery
--
-- No INSERT/UPDATE/ALTER statement is included until the legacy column mapping
-- and physical photo inventory have been supplied and approved.
