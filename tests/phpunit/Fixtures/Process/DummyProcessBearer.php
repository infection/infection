<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Closure;
use Infection\Process\Runner\ProcessBearer;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;


final readonly class DummyProcessBearer implements ProcessBearer
{
    public function __construct(private Process $process, private bool $expectTimeOut, private Closure $terminateCallback)
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

    public function terminateProcess(): void
    {
        ($this->terminateCallback)();
    }
}
