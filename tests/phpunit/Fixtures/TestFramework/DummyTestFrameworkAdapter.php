<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework;

use Infection\AbstractTestFramework\TestFrameworkAdapter;

final class DummyTestFrameworkAdapter implements TestFrameworkAdapter
{
    public function getName(): string
    {
        return 'dummy';
    }

    public function testsPass(string $output): bool
    {
        return true;
    }

    public function hasJUnitReport(): bool
    {
        return false;
    }

    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage): array
    {
        return ['/bin/dummy'];
    }

    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions): array
    {
        return ['/bin/dummy'];
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        return 'foo';
    }
}
