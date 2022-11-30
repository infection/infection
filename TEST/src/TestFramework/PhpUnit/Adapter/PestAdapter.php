<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Adapter;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\MemoryUsageAware;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\SyntaxErrorAware;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class PestAdapter implements MemoryUsageAware, ProvidesInitialRunOnlyOptions, SyntaxErrorAware, TestFrameworkAdapter
{
    private const NAME = 'Pest';
    public function __construct(private PhpUnitAdapter $phpUnitAdapter)
    {
    }
    public function getName() : string
    {
        return self::NAME;
    }
    public function testsPass(string $output) : bool
    {
        if (preg_match('/Tests:\\s+(.*?)(\\d+\\sfailed)/i', $output) === 1) {
            return \false;
        }
        $isOk = preg_match('/Tests:\\s+(.*?)(\\d+\\spassed)/', $output) === 1;
        $isOkRisked = preg_match('/Tests:\\s+(.*?)(\\d+\\srisked)/', $output) === 1;
        return $isOk || $isOkRisked;
    }
    public function isSyntaxError(string $output) : bool
    {
        return preg_match('/(ParseError\\s*syntax error|Syntax Error for Pest)/i', $output) === 1;
    }
    public function hasJUnitReport() : bool
    {
        return $this->phpUnitAdapter->hasJUnitReport();
    }
    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage) : array
    {
        return $this->phpUnitAdapter->getInitialTestRunCommandLine($extraOptions, $phpExtraArgs, $skipCoverage);
    }
    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions) : array
    {
        return $this->phpUnitAdapter->getMutantCommandLine($coverageTests, $mutatedFilePath, $mutationHash, $mutationOriginalFilePath, sprintf('--colors=never %s', $extraOptions));
    }
    public function getVersion() : string
    {
        return $this->phpUnitAdapter->getVersion();
    }
    public function getInitialTestsFailRecommendations(string $commandLine) : string
    {
        return $this->phpUnitAdapter->getInitialTestsFailRecommendations($commandLine);
    }
    public function getMemoryUsed(string $output) : float
    {
        return $this->phpUnitAdapter->getMemoryUsed($output);
    }
    public function getInitialRunOnlyOptions() : array
    {
        return $this->phpUnitAdapter->getInitialRunOnlyOptions();
    }
}
