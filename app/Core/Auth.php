<?php

namespace App\Core;

use App\Enums\AccountStatus;

final class Auth
{
    private static ?array $user = null;

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }

        $id = Session::get('user_id');
        if (! $id) {
            return null;
        }

        self::$user = Database::fetch('SELECT * FROM users WHERE user_id = ?', [$id]);

        return self::$user;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user ? (int) $user['user_id'] : null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::fetch('SELECT * FROM users WHERE email = ?', [$email]);

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return false;
        }

        if ($user['status'] !== AccountStatus::ACTIVE->value) {
            return false;
        }

        Session::regenerate();
        Session::put('user_id', (int) $user['user_id']);
        self::$user = $user;

        return true;
    }

    public static function logout(): void
    {
        self::$user = null;
        Session::forget('user_id');
        Session::regenerate();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();

        return $user !== null && in_array($user['role'], $roles, true);
    }
}
