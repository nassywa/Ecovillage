-- EcoVillage runtime database user (REVIEW ONLY; DO NOT RUN UNCHANGED)
-- Execute this file only as a database administrator after approval.
-- Replace the placeholder password locally with a generated secret.
-- Do not commit the replaced file or put the secret in Git.

-- Deliberately fails if the account already exists; do not silently reuse an unreviewed account.
CREATE USER 'ecovillage_app'@'%' IDENTIFIED BY '<GENERATE-AND-REPLACE-WITH-A-UNIQUE-STRONG-PASSWORD>' REQUIRE SSL;

-- Runtime needs data access only. Schema changes belong to install/migration administration.
-- Grants are per current application table so legacy_* and migration-history tables remain private.
-- Replace __STAGING_DATABASE_NAME__ after confirming the Railway database name.
GRANT SELECT, INSERT, DELETE ON `__STAGING_DATABASE_NAME__`.`media` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE ON `__STAGING_DATABASE_NAME__`.`settings` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`sessions` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`users` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`office_hours` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`officials` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`stat_sections` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`stat_groups` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, DELETE ON `__STAGING_DATABASE_NAME__`.`stat_items` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`map_regions` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`map_points` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`news` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`places` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`gallery` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`report_categories` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`reports` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT ON `__STAGING_DATABASE_NAME__`.`report_updates` TO 'ecovillage_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `__STAGING_DATABASE_NAME__`.`messages` TO 'ecovillage_app'@'%';

-- Review the result with database/operations/verify_app_user.sql.
