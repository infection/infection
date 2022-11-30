<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Adapter;

use function escapeshellarg;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\MemoryUsageAware;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\SyntaxErrorAware;
use _HumbugBox9658796bb9f0\Infection\Config\ValueProvider\PCOVDirectoryProvider;
use _HumbugBox9658796bb9f0\Infection\TestFramework\AbstractTestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\CommandLineBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\InitialConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\MutationConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use _HumbugBox9658796bb9f0\Infection\TestFramework\VersionParser;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function trim;
use function version_compare;
class PhpUnitAdapter extends AbstractTestFrameworkAdapter implements MemoryUsageAware, ProvidesInitialRunOnlyOptions, SyntaxErrorAware
{
    public const COVERAGE_DIR = 'coverage-xml';
    public function __construct(string $testFrameworkExecutable, private string $tmpDir, private string $jUnitFilePath, private PCOVDirectoryProvider $pcovDirectoryProvider, InitialConfigBuilder $initialConfigBuilder, MutationConfigBuilder $mutationConfigBuilder, CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder, VersionParser $versionParser, CommandLineBuilder $commandLineBuilder, ?string $version = null)
    {
        parent::__construct($testFrameworkExecutable, $initialConfigBuilder, $mutationConfigBuilder, $argumentsAndOptionsBuilder, $versionParser, $commandLineBuilder, $version);
    }
    public function hasJUnitReport() : bool
    {
        return \true;
    }
    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage) : array
    {
        if ($skipCoverage === \false) {
            $extraOptions = trim(sprintf('%s --coverage-xml=%s --log-junit=%s', $extraOptions, $this->tmpDir . '/' . self::COVERAGE_DIR, $this->jUnitFilePath));
            if ($this->pcovDirectoryProvider->shallProvide()) {
                $phpExtraArgs[] = '-d';
                $phpExtraArgs[] = sprintf('pcov.directory=%s', escapeshellarg($this->pcovDirectoryProvider->getDirectory()));
            }
        }
        return parent::getInitialTestRunCommandLine($extraOptions, $phpExtraArgs, $skipCoverage);
    }
    public function testsPass(string $output) : bool
    {
        if (preg_match('/failures!/i', $output) === 1) {
            return \false;
        }
        if (preg_match('/errors!/i', $output) === 1) {
            return \false;
        }
        $isOk = preg_match('/OK\\s\\(/', $output) === 1;
        $isOkWithInfo = preg_match('/OK\\s?,/', $output) === 1;
        $isWarning = preg_match('/warnings!/i', $output) === 1;
        $isNoTestsExecuted = preg_match('/No tests executed!/i', $output) === 1;
        return $isOk || $isOkWithInfo || $isWarning || $isNoTestsExecuted;
    }
    public function isSyntaxError(string $output) : bool
    {
        return preg_match('/ParseError: syntax error/i', $output) === 1;
    }
    public function getMemoryUsed(string $output) : float
    {
        if (preg_match('/Memory: (\\d+(?:\\.\\d+))\\s*MB/', $output, $match) === 1) {
            return (float) $match[1];
        }
        return -1.0;
    }
    public function getName() : string
    {
        return 'PHPUnit';
    }
    public function getInitialTestsFailRecommendations(string $commandLine) : string
    {
        $recommendations = parent::getInitialTestsFailRecommendations($commandLine);
        if (version_compare($this->getVersion(), '7.2', '>=')) {
            $recommendations = sprintf("%s\n\n%s\n\n%s", "Infection runs the test suite in a RANDOM order. Make sure your tests do not have hidden dependencies.\n\n" . 'You can add these attributes to `phpunit.xml` to check it: <phpunit executionOrder="random" resolveDependencies="true" ...', 'If you don\'t want to let Infection run tests in a random order, set the `executionOrder` to some value, for example <phpunit executionOrder="default"', parent::getInitialTestsFailRecommendations($commandLine));
        }
        return $recommendations;
    }
    public function getInitialRunOnlyOptions() : array
    {
        return ['--configuration', '--filter', '--testsuite'];
    }
}
