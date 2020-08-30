<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Console;

use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Mutant\MutantExecutionResult;
use Infection\Tests\UnsupportedMethod;

final class FakeOutputFormatter implements OutputFormatter
{
    public function start(int $mutationCount): void
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function advance(MutantExecutionResult $executionResult, int $mutationCount): void
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function finish(): void
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }
}
