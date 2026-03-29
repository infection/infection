<?php

namespace Infection\E2ETests\PHPUnit_12_5\Covered;

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
