<?php

declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Adapter;


use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use Infection\TestFramework\TestFrameworkTypes;
use Symfony\Component\Process\Process;
use function Safe\preg_match;
use function Safe\sprintf;

/**
 * @internal
 */
final class ParatestAdapter implements MemoryUsageAware, ProvidesInitialRunOnlyOptions, TestFrameworkAdapter
{
    private const NAME = 'Paratest';

    private PhpUnitAdapter $phpUnitAdapter;

    public function __construct(PhpUnitAdapter $phpUnitAdapter)
    {
        $this->phpUnitAdapter = $phpUnitAdapter;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function testsPass(string $output): bool
    {
        return $this->phpUnitAdapter->testsPass($output);
    }

    public function hasJUnitReport(): bool
    {
        return $this->phpUnitAdapter->hasJUnitReport();
    }

    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage): array
    {
        return $this->phpUnitAdapter->getInitialTestRunCommandLine($extraOptions, $phpExtraArgs, $skipCoverage);
    }

    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions): array
    {
        return $this->phpUnitAdapter->getMutantCommandLine(
            $coverageTests,
            $mutatedFilePath,
            $mutationHash,
            $mutationOriginalFilePath,
            $extraOptions
        );
    }

    public function getVersion(): string
    {
        return $this->phpUnitAdapter->getVersion();
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        return $this->phpUnitAdapter->getInitialTestsFailRecommendations($commandLine);
    }

    public function getMemoryUsed(string $output): float
    {
        return $this->phpUnitAdapter->getMemoryUsed($output);
    }

    /**
     * @return string[]
     */
    public function getInitialRunOnlyOptions(): array
    {
        return $this->phpUnitAdapter->getInitialRunOnlyOptions();
    }

}

