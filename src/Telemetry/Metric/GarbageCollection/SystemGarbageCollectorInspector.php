<?php

declare(strict_types=1);

namespace Infection\Telemetry\Metric\GarbageCollection;

use Infection\CannotBeInstantiated;
use const PHP_VERSION_ID;

final class SystemGarbageCollectorInspector
{
    use CannotBeInstantiated;

    public static function create(): GarbageCollectorInspector
    {
        return self::isPhp83OrHigher()
            ? new Php83GarbageCollectorInspector()
            : new Php81GarbageCollectorInspector();
    }

    private static function isPhp83OrHigher(): bool
    {
        return PHP_VERSION_ID >= 83_000;
    }
}