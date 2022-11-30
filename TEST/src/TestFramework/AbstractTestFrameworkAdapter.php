<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\InitialConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\MutationConfigBuilder;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
abstract class AbstractTestFrameworkAdapter implements TestFrameworkAdapter
{
    public function __construct(private string $testFrameworkExecutable, private InitialConfigBuilder $initialConfigBuilder, private MutationConfigBuilder $mutationConfigBuilder, private CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder, private VersionParser $versionParser, private CommandLineBuilder $commandLineBuilder, private ?string $version = null)
    {
    }
    public abstract function testsPass(string $output) : bool;
    public abstract function getName() : string;
    public abstract function hasJUnitReport() : bool;
    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage) : array
    {
        return $this->getCommandLine($phpExtraArgs, $this->argumentsAndOptionsBuilder->buildForInitialTestsRun($this->buildInitialConfigFile(), $extraOptions));
    }
    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions) : array
    {
        return $this->getCommandLine([], $this->argumentsAndOptionsBuilder->buildForMutant($this->buildMutationConfigFile($coverageTests, $mutatedFilePath, $mutationHash, $mutationOriginalFilePath), $extraOptions, $coverageTests));
    }
    public function getVersion() : string
    {
        return $this->version ?? ($this->version = $this->retrieveVersion());
    }
    public function getInitialTestsFailRecommendations(string $commandLine) : string
    {
        return sprintf('Check the executed command to identify the problem: %s', $commandLine);
    }
    protected function buildInitialConfigFile() : string
    {
        return $this->initialConfigBuilder->build($this->getVersion());
    }
    protected function buildMutationConfigFile(array $tests, string $mutantFilePath, string $mutationHash, string $mutationOriginalFilePath) : string
    {
        return $this->mutationConfigBuilder->build($tests, $mutantFilePath, $mutationHash, $mutationOriginalFilePath, $this->getVersion());
    }
    private function getCommandLine(array $phpExtraArgs, array $testFrameworkArgs) : array
    {
        return $this->commandLineBuilder->build($this->testFrameworkExecutable, $phpExtraArgs, $testFrameworkArgs);
    }
    private function retrieveVersion() : string
    {
        $testFrameworkVersionExecutable = $this->commandLineBuilder->build($this->testFrameworkExecutable, [], ['--version']);
        $process = new Process($testFrameworkVersionExecutable);
        $process->mustRun();
        return $this->versionParser->parse($process->getOutput());
    }
}
