<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Closure;
use Infection\Process\Runner\ProcessBearer;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;


final class DummyProcessBearer implements ProcessBearer
{
    public function __construct(private readonly Process $process, private readonly bool $expectTimeOut, private readonly Closure $terminateCallback)
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
