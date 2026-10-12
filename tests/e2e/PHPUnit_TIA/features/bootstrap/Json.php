<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use JsonException;
use function json_decode;
use const JSON_THROW_ON_ERROR;

final class Json
{
    use CannotBeInstantiated;

    /**
     * @throws JsonException
     */
    public static function decode(string $json): mixed
    {
        return json_decode(
            $json,
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
