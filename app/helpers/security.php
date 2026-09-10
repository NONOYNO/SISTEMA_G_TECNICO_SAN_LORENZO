<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize(mixed $value): mixed
{
    if (is_array($value)) {
        $clean = [];
        foreach ($value as $key => $item) {
            $cleanKey = is_string($key) ? trim(strip_tags($key)) : $key;
            $clean[$cleanKey] = sanitize($item);
        }

        return $clean;
    }

    if (is_string($value)) {
        return trim(strip_tags($value));
    }

    return $value;
}

function regenerate_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
