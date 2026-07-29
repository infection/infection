<?php

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\TestFramework\Common\LazyMutantEvaluationPipe;
use Infection\TestFramework\Contracts\CompositeInitialRunResults;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use function array_map;
use function implode;
use function sprintf;

final class CompositeTestFramework implements TestFramework
{
    /**
     * @param non-empty-list<TestFramework> $decoratedTestFrameworks
     */
    public function __construct(
        private array $decoratedTestFrameworks,
    ) {
    }

    public function getName(): string
    {
        return sprintf(
            'Composite(%s)',
            implode('", "', $this->decoratedTestFrameworks),
        );
    }

    public function getVersion(): string
    {
        return implode('", "', $this->decoratedTestFrameworks)
    }

    public function checkRequirements(): void
    {
        foreach ($this->decoratedTestFrameworks as $decoratedTestFramework) {
            $decoratedTestFramework->checkRequirements();
        }
    }

    public function executeInitialRun(): InitialRunResults|CompositeInitialRunResults
    {
        return new CompositeInitialRunResults(
            array_map(
                $this->decoratedTestFrameworks,
                static fn (TestFramework $testFramework): InitialRunResults => $testFramework->executeInitialRun(),
            ),
        );
    }

    public function test(Mutant $mutant): MutantExecutionResult|MutantEvaluationPipe
    {
        return LazyMutantEvaluationPipe::merge(
            array_map(
                $this->decoratedTestFrameworks,
                static fn (TestFramework $testFramework) => $testFramework->test($mutant),
            ),
        );
    }
}