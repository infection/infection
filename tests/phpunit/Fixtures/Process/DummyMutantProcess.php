<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResultFactory;
use Infection\Process\MutantProcess;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

final class DummyMutantProcess extends MutantProcess
{
    public function __construct(
        private Process $process,
        Mutant $mutant,
        MutantExecutionResultFactory $mutantExecutionResultFactory,
        private bool $expectTimeOut
    ) {
        parent::__construct($process, $mutant, $mutantExecutionResultFactory);
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
