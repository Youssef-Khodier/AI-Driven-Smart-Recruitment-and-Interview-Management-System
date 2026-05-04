<?php

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (! $token) {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf', $token);
        }

        return $token;
    }

    public static function validate(?string $token): void
    {
        if (! is_string($token) || ! hash_equals(self::token(), $token)) {
            throw new HttpException(419, 'Your session expired. Please try again.');
        }
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }
}
