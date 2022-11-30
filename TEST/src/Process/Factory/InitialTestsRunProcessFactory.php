<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Factory;

use _HumbugBox9658796bb9f0\Composer\InstalledVersions;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\Process\OriginalPhpProcess;
use function method_exists;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
use function version_compare;
class InitialTestsRunProcessFactory
{
    public function __construct(private TestFrameworkAdapter $testFrameworkAdapter)
    {
    }
    public function createProcess(string $testFrameworkExtraOptions, array $phpExtraOptions, bool $skipCoverage) : Process
    {
        $processClass = $skipCoverage ? Process::class : OriginalPhpProcess::class;
        $process = new $processClass($this->testFrameworkAdapter->getInitialTestRunCommandLine($testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage));
        $process->setTimeout(null);
        if (method_exists($process, 'inheritEnvironmentVariables') && version_compare((string) InstalledVersions::getPrettyVersion('symfony/console'), 'v4.4', '<')) {
            $process->inheritEnvironmentVariables();
        }
        return $process;
    }
}
