<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Tracing;

use Infection\TestFramework\Coverage\Trace;
use PHPUnit\Framework\Assert;

final class TraceAssertion
{
    public static function assertEquals(
        Trace $expected,
        Trace $actual,
    ): void
    {
        Assert::assertEquals(
            self::collectState($expected),
            self::collectState($actual),
        );
    }

    private static function collectState(Trace $trace): array
    {
        return [
            'sourceFileInfo' => $trace->getSourceFileInfo(),
            'realPath' => $trace->getRealPath(),
            'relativePathname' => $trace->getRelativePathname(),
            'hasTests' => $trace->hasTests(),
            'tests' => $trace->getTests(),
        ];
    }
}
