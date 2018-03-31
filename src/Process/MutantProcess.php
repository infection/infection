<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process;

use Infection\Mutant\Mutant;
use Infection\Mutant\MutantInterface;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Process\Process;

class MutantProcess
{
    const CODE_KILLED = 0;
    const CODE_ESCAPED = 1;
    const CODE_ERROR = 2;
    const CODE_TIMED_OUT = 3;
    const CODE_NOT_COVERED = 4;

    const PROCESS_OK = 0;
    const PROCESS_GENERAL_ERROR = 1;
    const PROCESS_MISUSE_SHELL_BUILTINS = 2;

    const NOT_FATAL_ERROR_CODES = [
        self::PROCESS_OK,
        self::PROCESS_GENERAL_ERROR,
        self::PROCESS_MISUSE_SHELL_BUILTINS,
    ];

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

    public function __construct(Process $process, MutantInterface $mutant, AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->process = $process;
        $this->mutant = $mutant;
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    private function isTimedOut(): bool
    {
        return $this->isTimedOut;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getMutant(): MutantInterface
    {
        return $this->mutant;
    }

    public function markTimeout()
    {
        $this->isTimedOut = true;
    }

    public function getResultCode(): int
    {
        if (!$this->getMutant()->isCoveredByTest()) {
            return self::CODE_NOT_COVERED;
        }

        if ($this->isTimedOut()) {
            return self::CODE_TIMED_OUT;
        }

        if (!in_array($this->getProcess()->getExitCode(), self::NOT_FATAL_ERROR_CODES, true)) {
            return self::CODE_ERROR;
        }

        if ($this->testFrameworkAdapter->testsPass($this->getProcess()->getOutput())) {
            return self::CODE_ESCAPED;
        }

        return self::CODE_KILLED;
    }
}
