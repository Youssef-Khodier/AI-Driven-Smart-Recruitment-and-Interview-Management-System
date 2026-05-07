<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $nameOrPath, array $params = []): string
{
    $base = Request::basePath();

    if (str_starts_with($nameOrPath, '/')) {
        return $base . $nameOrPath;
    }

    return $base . Router::path($nameOrPath, $params);
}

function csrf_field(): string
{
    return Csrf::field();
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
}

function auth_user(): ?array
{
    return Auth::user();
}

function old(string $key, mixed $default = ''): mixed
{
    $old = Session::flashed('old', []);

    return $old[$key] ?? $default;
}

function flash(string $key, mixed $default = null): mixed
{
    return Session::flashed($key, $default);
}

function error_list(): array
{
    return flash('errors', []);
}

function error(string $key): mixed
{
    $errors = error_list();

    return $errors[$key] ?? null;
}

function selected(mixed $actual, mixed $expected): string
{
    return (string) $actual === (string) $expected ? ' selected' : '';
}

function checked(mixed $actual): string
{
    return $actual ? ' checked' : '';
}

function str_limit(?string $value, int $limit = 90): string
{
    $value = (string) $value;

    if ((function_exists('mb_strlen') ? mb_strlen($value) : strlen($value)) <= $limit) {
        return $value;
    }

    return (function_exists('mb_substr') ? mb_substr($value, 0, $limit - 3) : substr($value, 0, $limit - 3)) . '...';
}
