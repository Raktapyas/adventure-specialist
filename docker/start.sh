#!/bin/bash
set -e

cd /var/www/html

echo ">>> Waiting for the database..."
attempt=1
until php -r '
$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: "3306";
$db   = getenv("DB_DATABASE");
$user = getenv("DB_USERNAME");
$pass = getenv("DB_PASSWORD");
try {
    new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [PDO::ATTR_TIMEOUT => 3]);
} catch (Throwable $e) {
    exit(1);
}' 2>/dev/null; do
    if [ "$attempt" -ge 30 ]; then
        echo "Database unreachable after 30 attempts, giving up."
        exit 1
    fi
    echo "    attempt $attempt failed, retrying in 2s..."
    sleep 2
    attempt=$((attempt + 1))
done
echo ">>> Database reachable."

echo ">>> Running migrations..."
php artisan migrate --force

# Seed only a brand-new database so redeploys never duplicate content.
USER_COUNT=$(php -r '
$p = new PDO(
    sprintf("mysql:host=%s;port=%s;dbname=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "3306", getenv("DB_DATABASE")),
    getenv("DB_USERNAME"),
    getenv("DB_PASSWORD")
);
echo $p->query("SELECT COUNT(*) FROM users")->fetchColumn();
')
if [ "$USER_COUNT" = "0" ]; then
    echo ">>> Fresh database detected — seeding content, roles and admin account..."
    php artisan db:seed --force
else
    echo ">>> Database already seeded (users: $USER_COUNT) — skipping seed."
fi

echo ">>> Linking public storage..."
php artisan storage:link || true

# Filament v3 serves admin JS/CSS from physical files in public/ — there is
# no fallback route. Regenerate on every boot so they always match vendor.
echo ">>> Publishing Filament assets..."
php artisan filament:assets

# Fix ownership on the mounted persistent disk (Render mounts it as root).
chown -R www-data:www-data storage bootstrap/cache || true

echo ">>> Caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo ">>> Booting Apache..."
exec apache2-foreground
