<?php

declare(strict_types=1);

/**
 * @param  mixed  $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    if (array_key_exists($key, $_ENV)) {
        $value = $_ENV[$key];
    } elseif (array_key_exists($key, $_SERVER)) {
        $value = $_SERVER[$key];
    } else {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
    }

    if ($value === null || $value === false) {
        return $default;
    }

    $normalized = is_string($value) ? strtolower(trim($value)) : $value;

    return match ($normalized) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'empty', '(empty)' => '',
        'null', '(null)' => null,
        default => $value,
    };
}
