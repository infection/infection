<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Closure;
use Infection\Process\Runner\ProcessBearer;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;
use function Safe\sprintf;

final class DummyProcessBearer implements ProcessBearer
{
    private $process;
    private $expectTimeOut;
    private $terminateCallback;

    public function __construct(Process $process, bool $expectTimeOut, Closure $terminateCallback)
    {
        $this->process = $process;
        $this->expectTimeOut = $expectTimeOut;
        $this->terminateCallback = $terminateCallback;
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
