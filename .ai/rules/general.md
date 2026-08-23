---
paths:
  - phpunit.xml
---

# General

## Tests must override Sail's DB_* env vars via <server> entries
compose.yaml injects real DB_* env vars into the Sail container. PHPUnit <env> (even force="true") does not update $_SERVER, and Laravel's env() reads $_SERVER first — so tests silently connected to the dev "laravel" DB and RefreshDatabase wiped it on every run. phpunit.xml therefore pins DB_CONNECTION/DB_HOST/DB_PORT/DB_DATABASE with BOTH <env force> and <server> entries pointing at the dedicated "testing" database. If you add more DB_* vars to compose.yaml, mirror them in phpunit.xml with <server>.
