<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\UserRepository;

final class Auth
{
    private static ?array $cachedUser = null;

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function id(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;
        if (is_int($id)) {
            return $id;
        }

        if (is_string($id) && ctype_digit($id)) {
            return (int) $id;
        }

        return null;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        self::$cachedUser = null;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
        self::$cachedUser = null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $repository = new UserRepository();
        $user = $repository->findById((int) self::id());
        self::$cachedUser = $user ?: null;

        return self::$cachedUser;
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            flash('error', 'Necesitas iniciar sesión para acceder a esta sección.');
            redirect('login');
        }
    }

    public static function requireRole(array|string $roles): void
    {
        self::requireAuth();

        $allowedRoles = is_array($roles) ? $roles : [$roles];
        $user = self::user();
        if ($user === null || !in_array($user['role'], $allowedRoles, true)) {
            flash('error', 'No tienes permisos para realizar esta acción.');
            redirect('dashboard');
        }
    }
}
