<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Tests\UnsupportedMethod;

class FakeTestFrameworkAdapter implements TestFrameworkAdapter
{
    public function getName(): string
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function testsPass(string $output): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function hasJUnitReport(): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage): array
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions): array
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getVersion(): string
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
