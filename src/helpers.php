<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    if ($path === '') {
        return BASE_PATH;
    }

    return BASE_PATH . '/' . ltrim($path, '/');
}

function config(?string $key = null, mixed $default = null): mixed
{
    $config = $GLOBALS['app_config'] ?? [];

    if ($key === null) {
        return $config;
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function str_lower(string $value): string
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);
}

function str_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
}

function redirect(string $route, array $params = []): never
{
    header('Location: ' . url_for($route, $params));
    exit;
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function current_route(): string
{
    return (string) ($_GET['route'] ?? 'home');
}

function url_for(string $route, array $params = []): string
{
    $baseUrl = app_base_url();
    $query = http_build_query(array_merge(['route' => $route], $params));
    if ($baseUrl === '') {
        return 'index.php?' . $query;
    }

    return $baseUrl . '/index.php?' . $query;
}

function asset_url(string $path): string
{
    $baseUrl = app_base_url();
    $cleanPath = ltrim($path, '/');

    if ($baseUrl === '') {
        return $cleanPath;
    }

    return $baseUrl . '/' . $cleanPath;
}

function app_base_url(): string
{
    $baseUrl = trim((string) config('app.base_url', ''));
    if ($baseUrl === '' || $baseUrl === '/') {
        return '';
    }

    if ($baseUrl[0] !== '/') {
        $baseUrl = '/' . $baseUrl;
    }

    return rtrim($baseUrl, '/');
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function consume_flash(): array
{
    $flash = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);

    return $flash;
}

function set_form_state(string $form, array $input = [], array $errors = []): void
{
    $_SESSION['_forms'][$form] = [
        'input' => $input,
        'errors' => $errors,
    ];
}

function consume_form_state(string $form): array
{
    $state = $_SESSION['_forms'][$form] ?? [
        'input' => [],
        'errors' => [],
    ];

    unset($_SESSION['_forms'][$form]);

    return $state;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    if ($token === null || $token === '') {
        return false;
    }

    $stored = $_SESSION['_csrf_token'] ?? null;
    if (!is_string($stored) || $stored === '') {
        return false;
    }

    return hash_equals($stored, $token);
}

function require_csrf_or_abort(): void
{
    $token = $_POST['_token'] ?? null;
    if (!verify_csrf_token(is_string($token) ? $token : null)) {
        http_response_code(419);
        echo 'Solicitud inválida. Token CSRF incorrecto.';
        exit;
    }
}

function is_active_route(string $route): bool
{
    return current_route() === $route;
}
