<?php

declare(strict_types=1);

namespace Infection\MutationTesting;

use Infection\Ast\AstCollector;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\MutationGenerator;
use Infection\MutationTesting\MutationAnalyzer\MutationAnalyzer;
use Infection\PhpParser\UnparsableFile;
use Infection\Source\Collector\SourceCollector;
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\Coverage\JUnit\TestFileNameNotFoundException;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\ReportLocationThrowable;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use Infection\TestFramework\Coverage\XmlReport\InvalidCoverage;
use function Pipeline\take;

/**
 * @internal
 */
final readonly class MutationTestingRunner
{
    public function __construct(
        private SourceCollector $sourceCollector,
        private AstCollector $astCollector,
        private MutationGenerator $mutationGenerator,
        private MutationAnalyzer $mutationAnalyzer,
    ) {

    }

    /**
     * @throws UnparsableFile
     * @throws InvalidCoverage
     * @throws NoSourceFound
     * @throws NoReportFound
     * @throws TooManyReportsFound
     * @throws ReportLocationThrowable
     * @throws TestFileNameNotFoundException
     *
     * @return list<MutantExecutionResult>
     */
    private function runMutationAnalysis(): array
    {
        return take($this->sourceCollector->collect())
            ->map($this->astCollector->generate(...))
            ->unpack($this->mutationGenerator->generate(...))
            ->map($this->mutationAnalyzer->analyze(...))
            ->toList();
    }
}
