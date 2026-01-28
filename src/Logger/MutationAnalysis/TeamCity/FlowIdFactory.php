<?php

declare(strict_types=1);

namespace Infection\Logger\MutationAnalysis\TeamCity;

use Infection\CannotBeInstantiated;

/**
 * @internal
 */
final class FlowIdFactory
{
    use CannotBeInstantiated;

    public static function create(string $value): string
    {
        // Any hash which avoids collision, is fast and deterministic will do.
        return hash('xxh3', $value);
    }
}