<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Infection\TestFramework\Contracts\MutantProcess;
use Infection\TestFramework\Contracts\MutantProcessResult;
use Infection\TestFramework\Contracts\DetectionStatus;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

final readonly class DummyMutantProcess implements MutantProcess
{
    public function __construct(
        private readonly Process $process,
        private readonly bool $expectTimeOut,
        private readonly DetectionStatus $detectionStatus,
    ) {
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

    public function markAsFinished(): void
    {
    }

    public function getResult(): MutantProcessResult
    {
        return new MutantProcessResult('', '', '', 0., 0., $this->detectionStatus);
    }
}
