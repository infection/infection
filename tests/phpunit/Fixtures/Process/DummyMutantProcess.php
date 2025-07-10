<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Infection\Mutant\Mutant;
use Infection\Mutant\TestFrameworkMutantExecutionResultFactory;
use Infection\Process\MutantProcess;
use Infection\Process\TestTokenHandler;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

final class DummyMutantProcess extends MutantProcess
{
    public function __construct(
        private Process $process,
        Mutant $mutant,
        TestFrameworkMutantExecutionResultFactory $mutantExecutionResultFactory,
        private bool $expectTimeOut,
        ?TestTokenHandler $testTokenHandler,
    ) {
        parent::__construct($process, $mutant, $mutantExecutionResultFactory, $testTokenHandler);
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function markAsTimedOut(): void
    {
        if (!$this->expectTimeOut) {
            Assert::fail(sprintf(
                'Did not expect "%s()" to be called',
                __FUNCTION__
            ));
        }
    }
}
