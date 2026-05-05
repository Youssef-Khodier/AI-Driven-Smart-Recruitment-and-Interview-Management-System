<?php

namespace App\Core;

final class Config
{
    private static array $values = [];

    public static function load(string $root): void
    {
        self::$values = [
            'APP_NAME' => 'SRIM',
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'true',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'srim',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'SESSION_LIFETIME' => '120',
            'FIRST_HR_ADMIN_NAME' => 'SRIM HR Admin',
            'FIRST_HR_ADMIN_EMAIL' => 'hr.admin@example.com',
            'FIRST_HR_ADMIN_PASSWORD' => 'password',
            'CANDIDATE_RETENTION_DAYS' => '365',
        ];

        $env = $root . '/.env';
        if (! is_file($env)) {
            return;
        }

        foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            self::$values[trim($key)] = trim(trim($value), "\"'");
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$values[$key] ?? $default;
    }

    public static function database(): array
    {
        return [
            'host' => self::get('DB_HOST'),
            'port' => self::get('DB_PORT'),
            'database' => self::get('DB_DATABASE'),
            'username' => self::get('DB_USERNAME'),
            'password' => self::get('DB_PASSWORD'),
        ];
    }
}
