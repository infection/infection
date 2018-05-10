<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\Console\Util\PhpProcess;
use Infection\Mutant\MutantInterface;
use Infection\Process\MutantProcess;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
class ProcessBuilder
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
     * @param array $phpExtraOptions
     *
     * @return Process
     */
    public function getProcessForInitialTestRun(
        string $testFrameworkExtraOptions,
        bool $skipCoverage,
        array $phpExtraOptions = []
    ): Process {
        $includeArgs = PHP_SAPI == 'phpdbg';

        // If we're expecting to receive a code coverage, test process must run in a vanilla environment
        $processType = $skipCoverage ? Process::class : PhpProcess::class;

        /** @var PhpProcess|Process $process */
        $process = new $processType(
            $this->testFrameworkAdapter->getExecutableCommandLine(
                $this->testFrameworkAdapter->buildInitialConfigFile(),
                $testFrameworkExtraOptions,
                $includeArgs,
                $phpExtraOptions
            )
        );

        $process->setTimeout(null); // ignore the default timeout of 60 seconds
        $process->inheritEnvironmentVariables();

        return $process;
    }

    public function getProcessForMutant(MutantInterface $mutant, string $testFrameworkExtraOptions = ''): MutantProcess
    {
        $process = new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine(
                $this->testFrameworkAdapter->buildMutationConfigFile($mutant),
                $testFrameworkExtraOptions
            )
        );

        $process->setTimeout($this->timeout);
        $process->inheritEnvironmentVariables();

        return new MutantProcess($process, $mutant, $this->testFrameworkAdapter);
    }
}
