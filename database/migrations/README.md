# Legacy database migration

## Scope

The source schema audited in XAMPP contains these tables:

| Legacy table | Current destination | Result |
|---|---|---|
| `admins` | `users` | Admin row and `password_hash` copied unchanged; username becomes a temporary `@legacy.invalid` email. |
| `reports` | `reports` | IDs, tickets, descriptions, locations, notes, timestamps, and current status preserved. Legacy `Diteruskan` becomes current `Diproses`. |
| `report_updates` | `report_updates` | All 9 events preserved; `new_status` is retained, including historical `Diteruskan`. |
| `village_contact` | `settings` | Phone, email, address, Facebook, and Instagram copied only when target keys are absent. |
| `village_profile` | `settings` | Name, region, description, history, vision, and mission copied only when target keys are absent. |
| `village_information` | `stat_sections`, `stat_groups`, `stat_items` | Imported under section code `L`, one group per source row. |
| `village_gallery` | `gallery` | Records copied; media remains unlinked unless a verified file migration is added. |

The audited legacy database has 1 admin, 7 reports, 9 report updates, 1 contact row, 1 profile row, 1 information row, and 0 gallery rows. The two report images and the village logo paths were not found in the workspace, so no `media` rows are fabricated.

## Safe procedure

1. Create a new staging database, for example `ecovillage_migration_test`.
2. Take a logical backup of the original database and verify that the dump can be read. Do not run the dump against the original.
3. Install the current schema into the new staging database using `database/schema.sql`.
4. Restore the legacy tables into the staging database with the `legacy_` prefix. Do not restore them as `reports` or `report_updates`, because those names belong to the current schema.
5. Review the source counts and file inventory.
6. Run `001_legacy_to_current.sql` in the staging database.
7. Run `verification.sql`. The expected report count is 7 and expected history count is 9 for the audited snapshot.
8. Run the application against staging and manually test admin login, report listing/detail/status history, profile/contact, gallery, and media behavior.
9. Only after review, create a fresh backup of the real target and execute the same migration against a separately prepared target database. Never use `/setup` for this migration.

## Backup and rollback

Before any production cutover, create a timestamped `mysqldump --single-transaction --routines --triggers` backup and test restoring it to a disposable database. Rollback is a database restore/cutover to the pre-migration snapshot, not a destructive reverse SQL script. The migration itself does not modify legacy source tables and can be retried on a fresh target.

## Duplicate prevention

The `ecovillage_migration_history` table records completion. Target primary keys, unique tickets, unique settings keys, unique stat group slugs, and `NOT EXISTS` guards make rerunning the script a no-op for already imported rows. A rerun must still be performed only on the same reviewed staging target; do not use this as permission to overwrite an existing production database.

## Media limitation

The current application stores images as BLOBs in `media`, while legacy tables store relative filesystem paths. A future media pass must first provide the actual files and a manifest of path, MIME type, size, and checksum. Until then, `photo_id` and `image_id` remain `NULL`, preserving the report/gallery records without inventing media.
