<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Console;

use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Mutant\MutantExecutionResult;
use Infection\Tests\UnsupportedMethod;

final class FakeMutationAnalysisLogger implements MutationAnalysisLogger
{
    public function startAnalysis(int $mutationCount): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function finishEvaluation(MutantExecutionResult $executionResult, int $mutationCount): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function finishAnalysis(): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
