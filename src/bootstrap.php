<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once base_path('src/helpers.php');

$configFile = base_path('config/config.php');
if (!file_exists($configFile)) {
    http_response_code(500);
    echo 'Falta config/config.php. Duplica config/config.example.php y ajusta tus credenciales.';
    exit;
}

$GLOBALS['app_config'] = require $configFile;

$sessionName = (string) (config('security.session_name', 'vajrayana_session'));
if (session_status() === PHP_SESSION_NONE) {
    session_name($sessionName);
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = base_path('src/' . str_replace('\\', '/', $relativeClass) . '.php');

    if (file_exists($path)) {
        require_once $path;
    }
});
