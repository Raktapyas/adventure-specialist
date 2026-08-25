---
paths:
  - 'public/js/**'
---

# Js

## Filament published assets are required — never delete
public/js/filament/** and public/css/filament/** are REQUIRED published assets, not stale build output. Filament v3 has NO fallback route: the admin panel loads JS/CSS from these physical files (URLs like /js/filament/filament/app.js). Deleting them breaks the whole dashboard (unstyled, no interactivity). Regenerate with `php artisan filament:assets` after composer updates; docker/start.sh also runs it on boot. Never gitignore or remove them.
