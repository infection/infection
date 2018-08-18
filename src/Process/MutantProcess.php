<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process;

use Infection\Mutant\MutantInterface;
use Infection\MutationInterface;
use Infection\Mutator\Util\Mutator;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class MutantProcess implements MutantProcessInterface
{
    public const CODE_KILLED = 0;
    public const CODE_ESCAPED = 1;
    public const CODE_ERROR = 2;
    public const CODE_TIMED_OUT = 3;
    public const CODE_NOT_COVERED = 4;

    private const PROCESS_OK = 0;
    private const PROCESS_GENERAL_ERROR = 1;
    private const PROCESS_MISUSE_SHELL_BUILTINS = 2;

    private const NOT_FATAL_ERROR_CODES = [
        self::PROCESS_OK,
        self::PROCESS_GENERAL_ERROR,
        self::PROCESS_MISUSE_SHELL_BUILTINS,
    ];

    /**
     * @var Process
     */
    private $process;

    /**
     * @var MutantInterface
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

    public function markTimeout(): void
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

        if (!\in_array($this->getProcess()->getExitCode(), self::NOT_FATAL_ERROR_CODES, true)) {
            return self::CODE_ERROR;
        }

        if ($this->testFrameworkAdapter->testsPass($this->getProcess()->getOutput())) {
            return self::CODE_ESCAPED;
        }

        return self::CODE_KILLED;
    }

    public function getMutator(): Mutator
    {
        return $this->getMutation()->getMutator();
    }

    public function getOriginalFilePath(): string
    {
        return $this->getMutation()->getOriginalFilePath();
    }

    public function getOriginalStartingLine(): int
    {
        return (int) $this->getMutation()->getAttributes()['startLine'];
    }

    private function getMutation(): MutationInterface
    {
        return $this->getMutant()->getMutation();
    }
}
