<?php

require __DIR__.'/../vendor/autoload.php';

// Dual-mode DB host: `sail artisan test` inside Docker resolves `mysql`,
// host `php artisan test` uses 127.0.0.1 via FORWARD_DB_PORT.
$canResolveMysql = @gethostbyname('mysql') !== 'mysql';

if ($canResolveMysql) {
    $_SERVER['DB_HOST'] = 'mysql';
    $_SERVER['DB_DATABASE'] = 'testing';
    $_ENV['DB_HOST'] = 'mysql';
    $_ENV['DB_DATABASE'] = 'testing';
    putenv('DB_HOST=mysql');
    putenv('DB_DATABASE=testing');
} else {
    $_SERVER['DB_HOST'] = '127.0.0.1';
    $_SERVER['DB_DATABASE'] = 'testing';
    $_ENV['DB_HOST'] = '127.0.0.1';
    $_ENV['DB_DATABASE'] = 'testing';
    putenv('DB_HOST=127.0.0.1');
    putenv('DB_DATABASE=testing');
}

// Ensure testing DB credentials match Sail defaults if not already set.
foreach (['DB_CONNECTION' => 'mysql', 'DB_PORT' => '3306', 'DB_USERNAME' => 'sail', 'DB_PASSWORD' => 'password'] as $k => $v) {
    if (empty($_SERVER[$k])) {
        $_SERVER[$k] = $v;
        $_ENV[$k] = $v;
        putenv("$k=$v");
    }
}
