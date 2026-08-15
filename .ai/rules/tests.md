---
paths:
  - 'tests/**'
---

# Tests

## Run tests via Sail (DB lives in Docker)
The app uses MySQL hosted in Docker (DB_HOST=mysql). PHPUnit cannot connect from the host shell because `mysql` only resolves inside the container. Always run tests with `./vendor/bin/sail php artisan test` (or `./vendor/bin/sail php artisan test --compact --filter=...`), never bare `php artisan test`.
