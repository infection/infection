<?php

declare(strict_types=1);

namespace Infection\Process;

use Infection\Mutant\Mutant;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Process\Process;

class MutantProcess
{
    const CODE_KILLED = 0;
    const CODE_ESCAPED = 1;
    const CODE_ERRORED = 2;
    const CODE_TIMED_OUT = 3;
    const CODE_NOT_COVERED = 4;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var Mutant
     */
    private $mutant;

    /**
     * @var bool
     */
    private $isTimedOut = false;

    /**
     * @var AbstractTestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    public function __construct(Process $process, Mutant $mutant, AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->process = $process;
        $this->mutant = $mutant;
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    /**
     * @return Process
     */
    public function getProcess() : Process
    {
        return $this->process;
    }

    /**
     * @return Mutant
     */
    public function getMutant() : Mutant
    {
        return $this->mutant;
    }

    public function markTimeout()
    {
        $this->isTimedOut = true;
    }

    public function isTimedOut(): bool
    {
        return $this->isTimedOut;
    }

    /**
     * @return bool
     */
    public function isIsTimedOut(): bool
    {
        return $this->isTimedOut;
    }

    public function getResultCode(): int
    {
        if (! $this->getMutant()->isCoveredByTest()) {
            return self::CODE_NOT_COVERED;
        }

        if ($this->testFrameworkAdapter->testsPass($this->getProcess()->getOutput())) {
            return self::CODE_ESCAPED;
        }

        if ($this->isTimedOut()) {
            return self::CODE_TIMED_OUT;
        }

        return self::CODE_KILLED;
    }
}