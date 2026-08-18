<?php
// Load .env for local development. On AWS, set env vars via EB environment properties.
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        // Strip surrounding single or double quotes (common .env convention)
        if (strlen($v) >= 2 && $v[0] === $v[-1] && ($v[0] === '"' || $v[0] === "'")) {
            $v = substr($v, 1, -1);
        }
        $_ENV[$k] = $v;
    }
}

return [
    'db_host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306',
    'db_name' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'scam_alert_db',
    'db_user' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
    'db_pass' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '',
];
