<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Closure;
use Infection\Process\MutantProcess;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;


final class DummyProcessBearer extends MutantProcess
{
    public function __construct(private Process $process, private bool $expectTimeOut)
    {
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
