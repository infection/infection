<?php

declare(strict_types=1);

namespace Infection\TestFramework\XML;

use ValueError;
use function sprintf;

final class InvalidXml extends ValueError
{

    public static function forString(string $xml): self {
        return new self(
            sprintf(
                'The string "%s" is not valid XML.',
                $xml,
            ),
        );
    }
}