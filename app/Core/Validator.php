<?php

namespace App\Core;

final class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldRules = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            $required = in_array('required', array_map(
                fn (mixed $rule): string => is_string($rule) ? explode(':', $rule, 2)[0] : (string) ($rule[0] ?? ''),
                $fieldRules
            ), true);

            if (! $required && ($value === null || $value === '')) {
                continue;
            }

            foreach ($fieldRules as $rule) {
                [$name, $parameter] = self::parseRule($rule);

                if ($name === 'required' && ($value === null || $value === '')) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                }

                if ($name === 'email' && $value !== null && $value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = ucfirst($field) . ' must be a valid email address.';
                }

                if ($name === 'min' && is_string($value) && self::length($value) < (int) $parameter) {
                    $errors[$field][] = ucfirst($field) . ' must be at least ' . $parameter . ' characters.';
                }

                if ($name === 'max' && is_string($value) && self::length($value) > (int) $parameter) {
                    $errors[$field][] = ucfirst($field) . ' may not be greater than ' . $parameter . ' characters.';
                }

                if ($name === 'numeric' && $value !== null && $value !== '' && ! is_numeric($value)) {
                    $errors[$field][] = ucfirst($field) . ' must be numeric.';
                }

                if ($name === 'in' && $value !== null && $value !== '' && ! in_array($value, (array) $parameter, true)) {
                    $errors[$field][] = ucfirst($field) . ' is invalid.';
                }

                if ($name === 'unique' && $value !== null && $value !== '' && ! self::isUnique($parameter, $value)) {
                    $errors[$field][] = ucfirst($field) . ' has already been taken.';
                }
            }

            $validated[$field] = is_string($value) ? trim($value) : $value;
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    
        return $validated;
    }

    private static function parseRule(mixed $rule): array
    {
        if (is_array($rule)) {
            return [$rule[0], count($rule) > 2 ? array_slice($rule, 1) : ($rule[1] ?? null)];
        }

        if (str_contains($rule, ':')) {
            [$name, $parameter] = explode(':', $rule, 2);

            return [$name, in_array($name, ['in', 'unique'], true) ? explode(',', $parameter) : $parameter];
        }

        return [$rule, null];
    }

    private static function isUnique(mixed $parameter, mixed $value): bool
    {
        $parts = (array) $parameter;
        $table = $parts[0] ?? null;
        $column = $parts[1] ?? null;

        if (! self::isIdentifier($table) || ! self::isIdentifier($column)) {
            return false;
        }

        return Database::fetch("SELECT {$column} FROM {$table} WHERE {$column} = ? LIMIT 1", [$value]) === null;
    }

    private static function isIdentifier(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value) === 1;
    }

    private static function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
