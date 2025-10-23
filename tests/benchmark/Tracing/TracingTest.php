<?php

declare(strict_types=1);

namespace Infection\Benchmark\Tracing;

use Closure;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\BeforeMethods;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

#[CoversNothing]
#[Group('benchmark')]
final class TracingTest extends TestCase
{
    private Closure $main;

    public function setUp(): void
    {
        $provideTraces = require __DIR__ . '/provide-traces-closure.php';

        $this->main = static fn () => $provideTraces(-1);
    }

    public function test_it_can_generate_traces(): void
    {
        $count = ($this->main)();

        self::assertGreaterThan(
            0,
            $count,
            'No trace was generated.',
        );
    }
}
