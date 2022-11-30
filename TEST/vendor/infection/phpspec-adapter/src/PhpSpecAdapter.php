<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec;

use function explode;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\CommandLine\ArgumentsAndOptionsBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder;
use const PHP_EOL;
use function preg_match;
use function sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
final class PhpSpecAdapter implements TestFrameworkAdapter
{
    public const COVERAGE_DIR = 'phpspec-coverage-xml';
    private const ERROR_REGEXPS = ['/Fatal error\\:/', '/Fatal error happened/i'];
    private string $testFrameworkExecutable;
    private ArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder;
    private InitialConfigBuilder $initialConfigBuilder;
    private MutationConfigBuilder $mutationConfigBuilder;
    private VersionParser $versionParser;
    private CommandLineBuilder $commandLineBuilder;
    private ?string $version;
    public function __construct(string $testFrameworkExecutable, InitialConfigBuilder $initialConfigBuilder, MutationConfigBuilder $mutationConfigBuilder, ArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder, VersionParser $versionParser, CommandLineBuilder $commandLineBuilder, ?string $version = null)
    {
        $this->testFrameworkExecutable = $testFrameworkExecutable;
        $this->initialConfigBuilder = $initialConfigBuilder;
        $this->mutationConfigBuilder = $mutationConfigBuilder;
        $this->argumentsAndOptionsBuilder = $argumentsAndOptionsBuilder;
        $this->versionParser = $versionParser;
        $this->commandLineBuilder = $commandLineBuilder;
        $this->version = $version;
    }
    public function hasJUnitReport() : bool
    {
        return \false;
    }
    public function testsPass(string $output) : bool
    {
        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) {
            if (preg_match('%not ok \\d+ - %', $line) > 0 && preg_match('%# TODO%', $line) === 0) {
                return \false;
            }
        }
        foreach (self::ERROR_REGEXPS as $regExp) {
            if (preg_match($regExp, $output) > 0) {
                return \false;
            }
        }
        return \true;
    }
    public function getName() : string
    {
        return 'PhpSpec';
    }
    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage) : array
    {
        return $this->getCommandLine($this->buildInitialConfigFile(), $extraOptions, $phpExtraArgs);
    }
    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions) : array
    {
        return $this->getCommandLine($this->buildMutationConfigFile($coverageTests, $mutatedFilePath, $mutationHash, $mutationOriginalFilePath), $extraOptions, []);
    }
    public function getVersion() : string
    {
        return $this->version ?? ($this->version = $this->retrieveVersion());
    }
    public function getInitialTestsFailRecommendations(string $commandLine) : string
    {
        return sprintf('Check the executed command to identify the problem: %s', $commandLine);
    }
    private function buildInitialConfigFile() : string
    {
        return $this->initialConfigBuilder->build($this->getVersion());
    }
    private function buildMutationConfigFile(array $tests, string $mutantFilePath, string $mutationHash, string $mutationOriginalFilePath) : string
    {
        return $this->mutationConfigBuilder->build($tests, $mutantFilePath, $mutationHash, $mutationOriginalFilePath);
    }
    private function getCommandLine(string $configPath, string $extraOptions, array $phpExtraArgs) : array
    {
        $frameworkArgs = $this->argumentsAndOptionsBuilder->build($configPath, $extraOptions);
        return $this->commandLineBuilder->build($this->testFrameworkExecutable, $phpExtraArgs, $frameworkArgs);
    }
    private function retrieveVersion() : string
    {
        $testFrameworkVersionExecutable = $this->commandLineBuilder->build($this->testFrameworkExecutable, [], ['--version']);
        $process = new Process($testFrameworkVersionExecutable);
        $process->mustRun();
        return $this->versionParser->parse($process->getOutput());
    }
}
