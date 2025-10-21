<?php

declare(strict_types=1);

namespace Infection\Benchmark\Tracing;

use Closure;
use Infection\Benchmark\InstrumentorFactory;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\BeforeClassMethods;
use PhpBench\Attributes\BeforeMethods;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use function extension_loaded;

/**
 * To execute this test run `make benchmark_tracing`
 */
#[BeforeClassMethods('ensureXdebugIsNotLoaded')]
final class TracingBench
{
    private Closure $main;
    private int $result;

    public static function ensureXdebugIsNotLoaded(): void
    {
//        Assert::false(
//            extension_loaded('xdebug'),
//            'Xdebug has been detected, this will mess up with the results.',
//        );
    }

    public function setUp(): void
    {
        $provideTraces = require __DIR__ . '/provide-traces-closure.php';

        $this->main = static fn () => $provideTraces(-1);
    }

    #[BeforeMethods('setUp')]
    #[AfterMethods('tearDown')]
    public function benchTracing(): void
    {
        $this->result = ($this->main)();
    }

    public function tearDown(): void
    {
        Assert::greaterThan(
            $this->result,
            0,
            'No trace was generated.',
        );
    }
}
