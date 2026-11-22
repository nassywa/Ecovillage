-- EcoVillage runtime database user privilege verification (READ ONLY)
-- Run as a database administrator. Replace __STAGING_DATABASE_NAME__ first.

SHOW GRANTS FOR 'ecovillage_app'@'%';

-- GLOBAL LEVEL: any global privilege other than implicit USAGE is excessive.
-- Expected result: zero rows.
SELECT GRANTEE, PRIVILEGE_TYPE
FROM information_schema.USER_PRIVILEGES
WHERE GRANTEE = '''ecovillage_app''@''%'''
  AND PRIVILEGE_TYPE <> 'USAGE';

-- SCHEMA LEVEL: the account must have no schema-level grants anywhere.
-- Expected result: zero rows.
SELECT GRANTEE, TABLE_SCHEMA, PRIVILEGE_TYPE
FROM information_schema.SCHEMA_PRIVILEGES
WHERE GRANTEE = '''ecovillage_app''@''%''';

-- TABLE LEVEL OUTSIDE STAGING: no table grant may exist in another schema.
-- Expected result: zero rows.
SELECT GRANTEE, TABLE_SCHEMA, TABLE_NAME, PRIVILEGE_TYPE
FROM information_schema.TABLE_PRIVILEGES
WHERE GRANTEE = '''ecovillage_app''@''%'''
  AND TABLE_SCHEMA <> '__STAGING_DATABASE_NAME__';

-- COLUMN LEVEL: app_user.sql grants only whole-table privileges.
-- Expected result: zero rows.
SELECT GRANTEE, TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, PRIVILEGE_TYPE
FROM information_schema.COLUMN_PRIVILEGES
WHERE GRANTEE = '''ecovillage_app''@''%''';

-- ROUTINE LEVEL: the runtime does not use stored procedures or functions.
-- Expected result: zero rows.
SELECT GRANTEE, ROUTINE_SCHEMA, ROUTINE_NAME, PRIVILEGE_TYPE
FROM information_schema.ROUTINE_PRIVILEGES
WHERE GRANTEE = '''ecovillage_app''@''%''';

-- ROLE LEVEL: inherited roles could add privileges outside the matrix.
-- Expected result: zero rows.
SELECT GRANTEE, GRANTEE_HOST, ROLE_NAME, ROLE_HOST
FROM information_schema.APPLICABLE_ROLES
WHERE GRANTEE = 'ecovillage_app'
  AND GRANTEE_HOST = '%';

-- MANDATORY ROLES: mandatory roles apply globally and are not listed in
-- mysql.role_edges. Any configured value requires administrator review.
-- Expected result: zero rows.
SELECT @@GLOBAL.mandatory_roles AS mandatory_roles
WHERE COALESCE(@@GLOBAL.mandatory_roles, '') <> '';

-- ROLE GRANTS: inspect the grant graph directly as an administrator.
-- Expected result: zero rows.
SELECT FROM_USER, FROM_HOST, TO_USER, TO_HOST
FROM mysql.role_edges
WHERE TO_USER = 'ecovillage_app'
  AND TO_HOST = '%';

-- PROXY PRIVILEGES: proxying could impersonate another account.
-- Expected result: zero rows.
SELECT User, Host, Proxied_user, Proxied_host, with_grant
FROM mysql.proxies_priv
WHERE User = 'ecovillage_app'
  AND Host = '%';

-- TABLE LEVEL: compare actual grants with the exact runtime matrix.
-- Rows labelled MISSING or UNEXPECTED indicate a failed check.
WITH expected (table_name, privilege_type) AS (
    SELECT 'media', 'SELECT' UNION ALL SELECT 'media', 'INSERT' UNION ALL SELECT 'media', 'DELETE'
    UNION ALL SELECT 'settings', 'SELECT' UNION ALL SELECT 'settings', 'INSERT' UNION ALL SELECT 'settings', 'UPDATE'
    UNION ALL SELECT 'sessions', 'SELECT' UNION ALL SELECT 'sessions', 'INSERT' UNION ALL SELECT 'sessions', 'UPDATE' UNION ALL SELECT 'sessions', 'DELETE'
    UNION ALL SELECT 'users', 'SELECT' UNION ALL SELECT 'users', 'INSERT' UNION ALL SELECT 'users', 'UPDATE' UNION ALL SELECT 'users', 'DELETE'
    UNION ALL SELECT 'office_hours', 'SELECT' UNION ALL SELECT 'office_hours', 'INSERT' UNION ALL SELECT 'office_hours', 'UPDATE' UNION ALL SELECT 'office_hours', 'DELETE'
    UNION ALL SELECT 'officials', 'SELECT' UNION ALL SELECT 'officials', 'INSERT' UNION ALL SELECT 'officials', 'UPDATE' UNION ALL SELECT 'officials', 'DELETE'
    UNION ALL SELECT 'stat_sections', 'SELECT' UNION ALL SELECT 'stat_sections', 'INSERT' UNION ALL SELECT 'stat_sections', 'UPDATE' UNION ALL SELECT 'stat_sections', 'DELETE'
    UNION ALL SELECT 'stat_groups', 'SELECT' UNION ALL SELECT 'stat_groups', 'INSERT' UNION ALL SELECT 'stat_groups', 'UPDATE' UNION ALL SELECT 'stat_groups', 'DELETE'
    UNION ALL SELECT 'stat_items', 'SELECT' UNION ALL SELECT 'stat_items', 'INSERT' UNION ALL SELECT 'stat_items', 'DELETE'
    UNION ALL SELECT 'map_regions', 'SELECT' UNION ALL SELECT 'map_regions', 'INSERT' UNION ALL SELECT 'map_regions', 'UPDATE' UNION ALL SELECT 'map_regions', 'DELETE'
    UNION ALL SELECT 'map_points', 'SELECT' UNION ALL SELECT 'map_points', 'INSERT' UNION ALL SELECT 'map_points', 'UPDATE' UNION ALL SELECT 'map_points', 'DELETE'
    UNION ALL SELECT 'news', 'SELECT' UNION ALL SELECT 'news', 'INSERT' UNION ALL SELECT 'news', 'UPDATE' UNION ALL SELECT 'news', 'DELETE'
    UNION ALL SELECT 'places', 'SELECT' UNION ALL SELECT 'places', 'INSERT' UNION ALL SELECT 'places', 'UPDATE' UNION ALL SELECT 'places', 'DELETE'
    UNION ALL SELECT 'gallery', 'SELECT' UNION ALL SELECT 'gallery', 'INSERT' UNION ALL SELECT 'gallery', 'UPDATE' UNION ALL SELECT 'gallery', 'DELETE'
    UNION ALL SELECT 'report_categories', 'SELECT' UNION ALL SELECT 'report_categories', 'INSERT' UNION ALL SELECT 'report_categories', 'UPDATE' UNION ALL SELECT 'report_categories', 'DELETE'
    UNION ALL SELECT 'reports', 'SELECT' UNION ALL SELECT 'reports', 'INSERT' UNION ALL SELECT 'reports', 'UPDATE' UNION ALL SELECT 'reports', 'DELETE'
    UNION ALL SELECT 'report_updates', 'SELECT' UNION ALL SELECT 'report_updates', 'INSERT'
    UNION ALL SELECT 'messages', 'SELECT' UNION ALL SELECT 'messages', 'INSERT' UNION ALL SELECT 'messages', 'UPDATE' UNION ALL SELECT 'messages', 'DELETE'
), actual AS (
    SELECT TABLE_NAME AS table_name, PRIVILEGE_TYPE AS privilege_type
    FROM information_schema.TABLE_PRIVILEGES
    WHERE GRANTEE = '''ecovillage_app''@''%'''
      AND TABLE_SCHEMA = '__STAGING_DATABASE_NAME__'
)
SELECT 'MISSING' AS issue, e.table_name, e.privilege_type
FROM expected e
LEFT JOIN actual a ON a.table_name = e.table_name AND a.privilege_type = e.privilege_type
WHERE a.table_name IS NULL
UNION ALL
SELECT 'UNEXPECTED' AS issue, a.table_name, a.privilege_type
FROM actual a
LEFT JOIN expected e ON e.table_name = a.table_name AND e.privilege_type = a.privilege_type
WHERE e.table_name IS NULL
ORDER BY issue, table_name, privilege_type;
-- Expected result: zero rows. The query catches missing/extra privileges
-- and unknown tables within the staging schema. The schema-level query above
-- catches table grants on any other schema.
