<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework;

use Closure;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\TestFramework\Contracts\InitialTestsResult;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\Tests\UnsupportedMethod;

class FakeTestFramework implements TestFramework
{
    public function getName(): string
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getVersion(): string
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function checkRequirements(): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function executeInitialRun(?Closure $onProgress = null): InitialTestsResult
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function test(Mutant $mutant): MutantExecutionResult|MutantEvaluationPipe
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
