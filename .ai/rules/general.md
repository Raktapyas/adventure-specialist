---
paths:
  - phpunit.xml
  - docker-compose.yml
---

# General

## Tests must override Sail's DB_* env vars via <server> entries
compose.yaml injects real DB_* env vars into the Sail container. PHPUnit <env> (even force="true") does not update $_SERVER, and Laravel's env() reads $_SERVER first — so tests silently connected to the dev "laravel" DB and RefreshDatabase wiped it on every run. phpunit.xml therefore pins DB_CONNECTION/DB_HOST/DB_PORT/DB_DATABASE with BOTH <env force> and <server> entries pointing at the dedicated "testing" database. If you add more DB_* vars to compose.yaml, mirror them in phpunit.xml with <server>.

## Run artisan through Sail; keep storage owned by sail (UID 1000)
This app runs via Laravel Sail. On the host, always use ./vendor/bin/sail artisan ... instead of plain php artisan — DB_HOST=mysql only resolves inside the Docker network, so host-run artisan fails with "getaddrinfo for mysql failed" (this is noise, not an app bug). Web server runs as sail (UID 1000). If storage/framework 500s with "tempnam(): file created in the system's temporary directory", ownership drifted (e.g. files created as root or old UID 1337): fix with docker exec adventure-specialist-laravel.test-1 chown -R sail:sail /var/www/html/storage /var/www/html/bootstrap/cache
