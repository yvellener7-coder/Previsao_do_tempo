<?php

if (!function_exists('starts_with')) {
    function starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

// Load local .env file in environments where vars are not injected by the web server.
$envPath = dirname(__DIR__, 2) . '/.env';
if (is_file($envPath) && is_readable($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '') {
            continue;
        }

        $value = trim($value, "\"'");

        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'tempo_escolar',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
];
