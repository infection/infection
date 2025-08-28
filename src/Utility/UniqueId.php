<?php

declare(strict_types=1);

namespace Infection\Utility;

use Infection\CannotBeInstantiated;
use function bin2hex;
use function random_bytes;

final class UniqueId
{
    use CannotBeInstantiated;

    public static function generate(): string
    {
        return bin2hex(random_bytes(6));
    }
}
