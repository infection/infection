<?php
/**
 * Copyright © 2017 Maks Rafalko
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

    public function getProcessForInitialTestRun(string $testFrameworkExtraOptions = ''): Process
    {
        $configPath = $this->testFrameworkAdapter->buildInitialConfigFile();

        return $this->getProcess($configPath, $testFrameworkExtraOptions);

        // TODO debug why processBuilder does not work with env
        // TODO read and add -vvv
        /*
        $processBuilder = new SymfonyProcessBuilder([
        $this->testFrameworkAdapter->getExecutableCommandLine()
        ]);

        return $processBuilder->getProcess();
         */
    }

    /**
     * @throws RuntimeException
     *
     * @param Mutant $mutant
     * @param string|null $testFrameworkExtraOptions
     *
     * @return MutantProcess
     */
    public function getProcessForMutant(Mutant $mutant, string $testFrameworkExtraOptions = ''): MutantProcess
    {
        $configPath = $this->testFrameworkAdapter->buildMutationConfigFile($mutant);

        $symfonyProcess = $this->getProcess($configPath, $testFrameworkExtraOptions, $this->timeout);

        return new MutantProcess($symfonyProcess, $mutant, $this->testFrameworkAdapter);
    }

    private function getProcess(string $configPath, string $testFrameworkExtraOptions, int $timeout = null): Process
    {
        return new Process(
            $this->testFrameworkAdapter->getExecutableCommandLine($configPath, $testFrameworkExtraOptions),
            null,
            array_replace($_ENV, $_SERVER),
            null,
            $timeout
        );
    }
}
