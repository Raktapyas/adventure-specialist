---
paths:
  - 'database/seeders/**'
---

# Seeders

## AdminUserSeeder provides the admin login
AdminUserSeeder creates the Filament admin account (default admin@example.com / password123, overridable via ADMIN_EMAIL/ADMIN_PASSWORD env). It is idempotent (firstOrCreate on email) and registered first in DatabaseSeeder. There is no other admin user seeder — without it the /admin panel has no login account.

## SyncLegacyMediaSeeder mirrors media:register-legacy
SyncLegacyMediaSeeder scans public/assets/images and registers missing files as legacy Media rows (is_legacy=true, disk/storage_path null, host-relative path /assets/images/...). It mirrors app/Console/Commands/RegisterLegacyMedia.php (media:register-legacy) — keep both in sync. Idempotent (skips existing paths); run it after import:legacy-data, which truncates the media table.
