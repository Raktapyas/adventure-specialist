# Phase 3 — Architecture Audit Report

**Project:** Adventure Specialist Travel (Laravel 13 + Tailwind v4 + Alpine)
**Status:** Read-only audit. No files were modified during analysis (working tree clean at `bb8a69f`).

---

## 1. Stack summary

- **Framework:** Laravel 13.24, PHP ^8.3, sqlite (`database/database.sqlite`, gitignored).
- **Frontend:** Tailwind CSS v4 (`@tailwindcss/vite` + typography plugin), Alpine 3.15, Vite 8, bunny fonts (Mulish/Fraunces).
- **Packages:** only the framework + tinker. **No auth, admin, filament, spatie, sanctum, or debug tooling installed** (verified via `composer show`).
- **Env:** `APP_ENV=local`, `SESSION_DRIVER=database`, `FILESYSTEM_DISK=local`, `QUEUE/CACHE=database`, `APP_DEBUG=true`.

## 2. Routing & URL architecture

All public routes live in the `canonical` middleware group (`EnsureTrailingSlash`):

| Pattern | Name | Controller |
|---|---|---|
| `/` | `home` | `HomeController@__invoke` |
| `/about-us`, `/about-us/{slug}` | `pages.index/show` | `PageController@show` |
| `/ast-services`, `/ast-services/{slug}`, `/ast-services/{parent}/{child}` | `services.*` | `ServiceController` |
| `/destination`, `/destination/{slug}`, `/{p}/{c}`, `/{p}/{c}/{gc}` | `destinations.*` | `DestinationController` |
| `/nepal`, `/nepal/{slug}` (+nested) | `destinations.nepal.*` | `DestinationController` |
| `/special-package`, `/special-package/{slug}` | `packages.*` | `PackageController` |
| `/gallery` | `gallery` | `GalleryController` |
| `/contact` GET/POST, `/contact/managing-director` | `contact.*` | `ContactController` |
| `/up` | health | framework |

Legacy WordPress URLs `/services*`, `/destinations*`, `/packages*`, `/about*` 301-redirect to the new canonical paths (`routes/web.php:63–93`).

- `EnsureTrailingSlash` 301s any GET path without a trailing slash; exempts `/`, assets, `/build/`, `/storage/`, `/up`.
- `getPath()` / `slugChain()` / `resolvePath()` live on the `Destination` and `Service` models (duplicated logic → candidate for a shared trait).
- Controllers verify the request path against the model's canonical path and 301 `redirect()->away($canonical)` on mismatch. **This is what keeps URLs stable even if a slug is later edited** and must be preserved by any admin CRUD.
- **Verified: 84/84 canonical URLs return 200** (8 pages + 25 services + 41 destinations + 4 packages + fixed pages). `/admin` is currently a 404 → no conflict with the future admin area.

## 3. Database schema

All 9 migrations applied. Content models use `protected $guarded = []`.

- `pages`, `services`, `destinations`: `id, parent_id, title, slug, excerpt, content, cover_image, sort_order, timestamps`.
- `packages`: same + `duration_days` (**never seeded — all NULL**).
- `gallery_images`: `id, image_url, caption, sort_order, timestamps`.
- `inquiries`: `id, name, email, phone, subject, message, timestamps`.
- `users` / `password_reset_tokens` / `sessions`: Laravel defaults. **0 users.**
- **No `is_published`/status column anywhere. `slug` is NOT unique in the schema** (collision risk). No `media`, no `slug_history`/redirects table.

## 4. Data & content quality

- Reference content migrated from WordPress; bodies are **raw legacy HTML rendered with `{!! !!}`** inside a `.prose-editorial` container (contains `<p>`, `<table>`, `<ul>`, and legacy `<img class="size-medium wp-image-...">` in `chitwan-tour` and `yala-peak-climbing`).
- No duplicate slugs within any model; 0 orphaned `parent_id` refs; 33/33 unique image refs exist on disk; zero external `/wp-content` image URLs in DB (external https anchors in content are legitimate links).
- **Duplicate titles:** services `Bheri River Rafting` (`bheri-river-rafting` + `bheri-river-rafting-2`) and `Sunkoshi River Rafting` (`-2`) — distinct slugs with genuinely different content; packages `Special Tour Package` x2.
- **Empty content** (show pages rely on sidebar children): destinations `tibet-tour, tibet-trek, bhutan-tour, bhutan-trekking, myanmar-tour, nepal, nepal-tour, langtang-region`; services `short-hiking, bungee-jumping`.
- `sort_order` contains duplicate zeros across children (stable ordering, not unique).

## 5. Seeders

- `Page/Service/DestinationSeeder`: `updateOrCreate(['slug'])` then overwrite `parent_id, title, excerpt, content, cover_image, sort_order`. **Parent lookup by slug each row** (assumes parents seed before children).
- `PackageSeeder`: `updateOrCreate(['slug'])`. `GallerySeeder`: `updateOrCreate(['caption'])`.
- ⚠️ **Critical:** a future `php artisan db:seed` will silently overwrite admin CMS edits. `caption` is a fragile identity key (duplicates if caption edited). `duration_days` is never set.

## 6. Media

- All images live in `public/assets/images/` (tracked in git); DB refs are `/assets/images/...`. Site serves from the web-root, **not** Laravel storage.
- `config/filesystems.php` has the `public` disk → `storage/app/public`, but **no `storage:link` symlink exists** and no S3 is configured. No media-library table → deleting an in-use image is undetectable.

## 7. Auth & security

- **No authentication installed** (no login routes, no policies, no Form Requests, 0 users). CSRF meta token is in the layout; the contact form is server-validated (`$request->validate`).
- `users` table + default `web` guard are ready; a user-created account is simply not promotable to admin yet.

## 8. Performance

- `NavComposer` (`View::composer('*')`) runs ~4 queries on every view.
- `HomeController` destination query lacks `with('children')`, yet `destination-card` calls `children->isNotEmpty()/count()` → N+1 (~5 extra queries on home).
- No caching, no queued jobs, no Form Requests.

## 9. Proposed admin architecture (Phase 3A+)

**Auth:** Laravel Breeze (blade, minimal) + `is_admin` flag on users; `EnsureIsAdmin` middleware; `/admin` route group with its own layout, kept **outside** the `canonical` group so trailing-slash handling doesn't interfere.

**Dashboard:** real DB metrics (pages 8, services 25, destinations 41, packages 4, gallery 1, inquiries 1).

**CRUD:** preserve legacy HTML as-is (raw `{!! !!}`) — no Markdown conversion; editor must keep `<table>`/`<img>`; server-side allowlist sanitization for admin input.

**Media:** keep `public/assets` for migrated images; add a `media` table (filename, path, alt, usage detection); enable `storage:link` for admin uploads.

**URL safety:** immutable slugs + a `slug_history` table for slug changes (auto-301). Keep canonical redirects + `EnsureTrailingSlash`; all 84 canonical URLs must stay stable.

**Seeders:** make idempotent (insert-if-missing only) so `db:seed` never overwrites CMS data; add `duration_days` to package seed data.

**New migrations:** `slug_history`, `media`, `users.is_admin`, unique `(parent_id, slug)`, `sort_order` normalization.

**Tests:** currently placeholder only; add feature tests for canonical redirects, admin CRUD, and seeder safety.
