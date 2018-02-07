<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

final class ProcessBuilder
{
    /**
     * @var AbstractTestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    /**
     * @var int
     */
    private $timeout;

    public function __construct(AbstractTestFrameworkAdapter $testFrameworkAdapter, int $timeout)
    {
        $this->testFrameworkAdapter = $testFrameworkAdapter;
        $this->timeout = $timeout;
    }

    /**
     * Creates process with enabled debugger as test framework is going to use in the code coverage.
     *
     * @param string $testFrameworkExtraOptions
     * @param bool $skipCoverage
     *
     * @return Process
     */
    public function getProcessForInitialTestRun(string $testFrameworkExtraOptions, bool $skipCoverage): Process
    {
        $includeArgs = PHP_SAPI === 'phpdbg' || $skipCoverage;

        return new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine(
                $this->testFrameworkAdapter->buildInitialConfigFile(),
                $testFrameworkExtraOptions,
                $includeArgs
            ),
            null,
            [],
            null,
            null
        );
    }

    /**
     * @throws RuntimeException
     *
     * @param Mutant $mutant
     * @param string $testFrameworkExtraOptions
     *
     * @return MutantProcess
     */
    public function getProcessForMutant(Mutant $mutant, string $testFrameworkExtraOptions = ''): MutantProcess
    {
        $process = new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine(
                $this->testFrameworkAdapter->buildMutationConfigFile($mutant),
                $testFrameworkExtraOptions
            ),
            null,
            array_replace($_ENV, $_SERVER),
            null,
            $this->timeout
        );

        $process->inheritEnvironmentVariables();

        return new MutantProcess($process, $mutant, $this->testFrameworkAdapter);
    }
}
