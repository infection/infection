<?php

declare(strict_types=1);

namespace Infection\Benchmark\MutationGenerator;

use Closure;
use Infection\Benchmark\InstrumentorFactory;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\BeforeClassMethods;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use function extension_loaded;
use const PHP_INT_MAX;

/**
 * To execute this test run `make benchmark_mutation_generator`
 */
final class MutationGeneratorBench
{
    private Closure $main;
    private int $count;

    public function setUp(): void
    {
        $generateMutations = require __DIR__ . '/generate-mutations-closure.php';

        $this->main = static fn () => $generateMutations(PHP_INT_MAX);
    }

    #[BeforeMethods('setUp')]
    #[AfterMethods('tearDown')]
    #[Iterations(5)]
    public function benchMutationGeneration(): void
    {
        $this->count = ($this->main)();
    }

    public function tearDown(): void
    {
        Assert::greaterThan(
            $this->count,
            0,
            'No mutation was generated.',
        );
    }
}
