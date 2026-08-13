---
paths:
  - 'app/Console/Commands/**'
---

# Commands

## Import legacy SQLite content via import:legacy-data
Use `php artisan import:legacy-data` to (re)import all legacy content from database/database.sqlite into MySQL. It truncates content tables (users, pages, services, destinations, packages, gallery_images, inquiries, redirects, media, media_usages) and re-imports with original IDs preserved, then re-syncs AUTO_INCREMENT. Ephemeral tables (sessions, cache, jobs, etc.) are skipped. WARNING: destructive — wipes existing content in MySQL. Reruns are safe because it's a full clean import each time.
