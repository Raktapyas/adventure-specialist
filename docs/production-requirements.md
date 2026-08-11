# Production Requirements

Read-only audit hardening for the Adventure Specialist production deployment.
This file documents the environment requirements surfaced by the production
audit (HEAD `339c5ff`, Phases 3A–3E) and the fixes applied for the audit
blockers. Secrets are never committed; every value below is supplied through
the deployment environment (`.env` on the server).

## PHP / Web Server Requirements (Blockers B1 + B2)

### Upload size limits (B2)

The application validates image uploads up to **5 MB** per file
(`config/media.php` → `max_upload_bytes`, `StoreMediaRequest` → `max:5120`).
The server's PHP configuration MUST accept at least that size, otherwise
uploads between the PHP cap and 5 MB fail before application validation runs.

Required `php.ini` values on the production server:

```ini
upload_max_filesize = 5M
post_max_size = 6M
memory_limit = 128M        ; Laravel's default guidance; verify against app needs
max_file_uploads = 20      ; at least the 10-file admin upload batch + margin
```

- `upload_max_filesize` MUST be `>= 5M` (application limit).
- `post_max_size` MUST be `>= 6M` (upload_max_filesize + form overhead).
- Do **not** lower the application validation to match the server — the app
  limit is the intended contract; raise the server limit instead.

Verify after configuring:

```bash
php -i | grep -E "upload_max_filesize|post_max_size"
```

### Trailing-slash redirects on Apache (B1)

`public/.htaccess` intentionally does **not** contain Laravel's default
"Redirect Trailing Slashes" block. The `EnsureTrailingSlash` middleware
(`app/Http/Middleware/EnsureTrailingSlash.php`) is the single owner of
canonical trailing-slash behavior:

- `/path` → 301 → `/path/`
- `/path/` → 200 (served directly)

Apache must pass every URL to the front controller untouched. If this file is
ever regenerated from a framework default, re-remove the trailing-slash strip
block, otherwise `/path/` → `/path` (Apache) → `/path/` (middleware) loops
forever with 301s.

On nginx no action is needed — `.htaccess` is ignored and the middleware
handles canonicalization directly.

## Production Environment (Blocker H1)

The production `.env` on the server MUST override the development defaults:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com        # real HTTPS origin, no trailing slash

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true              # forces the session cookie over HTTPS
SESSION_SAME_SITE=lax

LOG_CHANNEL=stack
LOG_STACK=daily                         # daily rotation, not unbounded "single"
LOG_LEVEL=warning                       # suppress debug noise in production

MAIL_MAILER=smtp                        # real mailer — see below
MAIL_HOST=<smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<smtp-username>
MAIL_PASSWORD=<smtp-password>           # deployment secret
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<noreply@your-domain.com>
MAIL_FROM_NAME="${APP_NAME}"

APP_KEY=<fresh 32-byte base64 key>      # generated on the server via artisan key:generate
```

Notes:

- `APP_KEY` is generated on the server; it is a secret and must never be
  committed or shared.
- `MAIL_*` values are supplied only through the deployment environment. The
  default `MAIL_MAILER=log` does not deliver password-reset or verification
  emails.
- `SESSION_SECURE_COOKIE=true` requires the site to be served over HTTPS (the
  `APP_URL` scheme must be `https://`).

## Post-Deploy Commands

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up   # after confirming maintenance mode is off
```

## Post-Deploy Verification

```bash
curl -I https://your-domain.com/gallery/    # expect 200, no redirect loop
curl -I https://your-domain.com/gallery     # expect 301 -> /gallery/
curl -I https://your-domain.com/about-us/   # expect 200
curl -I https://your-domain.com/up          # expect 200
php artisan migrate:status                  # all migrations applied
```
