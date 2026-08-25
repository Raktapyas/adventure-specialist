---
paths:
  - 'database/seeders/**'
---

# Seeders

## AdminUserSeeder provides the admin login
AdminUserSeeder creates the Filament admin account (default admin@example.com / password123, overridable via ADMIN_EMAIL/ADMIN_PASSWORD env). It is idempotent (firstOrCreate on email) and registered first in DatabaseSeeder. There is no other admin user seeder — without it the /admin panel has no login account.

## SyncLegacyMediaSeeder mirrors media:register-legacy
SyncLegacyMediaSeeder scans public/assets/images and registers missing files as legacy Media rows (is_legacy=true, disk/storage_path null, host-relative path /assets/images/...). It mirrors app/Console/Commands/RegisterLegacyMedia.php (media:register-legacy) — keep both in sync. Idempotent (skips existing paths); run it after import:legacy-data, which truncates the media table.

## Roles/permissions flow through RolesAndPermissionsSeeder
RolesAndPermissionsSeeder is the single source of truth for roles/permissions (idempotent, registered in DatabaseSeeder after AdminUserSeeder). sub-admin role = content only (pages/services/destinations/packages/gallery::image/media, view/create/update/delete prefixes — NO view_any_* per policies.md). super_admin holds every web-guard permission. Never run shield:generate (broken stubs). Roles created via the Filament Roles UI with a guard other than 'web' can never authenticate; the seeder purges them with mass deletes + explicit pivot cleanup because model->delete() crashes resolving users() against an unknown guard.

## Roles/permissions flow through RolesAndPermissionsSeeder
RolesAndPermissionsSeeder is the single source of truth for roles/permissions (idempotent, registered in DatabaseSeeder after AdminUserSeeder). sub-admin (staff manager) spec: Pages/Services/Destinations = view ONLY; Packages = view + create (staff add, admin curates); Media/Gallery = view + create + delete (no row editing); Inquiries = view + update only (no create/delete). NO users/roles/settings/hero-slides. super_admin holds every web-guard permission. Never run shield:generate (broken stubs). Roles created via Filament UI with guard != 'web' can never authenticate; seeder purges them via mass deletes + explicit pivot cleanup (model->delete() crashes resolving users() against an unknown guard).
