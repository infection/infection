<?php

namespace Infection\E2ETests\PHPUnit_11\Uncovered;

function formatName(string $firstName, string $lastName): string
{
    if (empty($firstName) && empty($lastName)) {
        return 'Anonymous';
    }

    if (empty($firstName)) {
        return $lastName;
    }

    if (empty($lastName)) {
        return $firstName;
    }

    return "{$firstName} {$lastName}";
}

function calculateDiscount(float $price, int $discountPercent): float
{
    if ($discountPercent < 0 || $discountPercent > 100) {
        throw new \InvalidArgumentException('Discount must be between 0 and 100');
    }

    if ($price <= 0) {
        return 0.0;
    }

    $discount = ($price * $discountPercent) / 100;
    return $price - $discount;
}

function isValidEmail(string $email): bool
{
    if (empty($email)) {
        return false;
    }

    return str_contains($email, '@') && str_contains($email, '.');
}
