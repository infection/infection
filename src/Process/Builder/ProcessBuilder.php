<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Builder;

use Infection\Mutant\MutantInterface;
use Infection\Php\ConfigBuilder;
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
        $includeArgs = PHP_SAPI == 'phpdbg' || $skipCoverage;

        $process = new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine(
                $this->testFrameworkAdapter->buildInitialConfigFile(),
                $testFrameworkExtraOptions,
                $includeArgs,
                $phpExtraOptions
            ),
            null,
            $includeArgs ? $this->getOurEnvironment() : null,
            null,
            null
        );

        if ($includeArgs) {
            $process->inheritEnvironmentVariables();
        }

        return $process;
    }

    public function getProcessForMutant(MutantInterface $mutant, string $testFrameworkExtraOptions = ''): MutantProcess
    {
        $process = new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine(
                $this->testFrameworkAdapter->buildMutationConfigFile($mutant),
                $testFrameworkExtraOptions
            ),
            null,
            $this->getOurEnvironment(),
            null,
            $this->timeout
        );

        $process->inheritEnvironmentVariables();

        return new MutantProcess($process, $mutant, $this->testFrameworkAdapter);
    }

    private $envCache;

    private function getOurEnvironment()
    {
        if (isset($this->envCache)) {
            return $this->envCache;
        }

        $this->envCache = array_replace($_ENV, $_SERVER);
        /*
         * We use our own php.ini for CLI, hence all other .ini files must be ignored.
         * For phpdbg no workarounds needed.
         */
        if ('phpdbg' != PHP_SAPI) {
            $this->envCache[ConfigBuilder::ENV_PHP_INI_SCAN_DIR] = '';
        }

        return $this->envCache;
    }
}
