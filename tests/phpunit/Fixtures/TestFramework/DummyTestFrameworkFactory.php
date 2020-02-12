<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\AbstractTestFramework\TestFrameworkAdapterFactory;

final class DummyTestFrameworkFactory implements TestFrameworkAdapterFactory
{
    public static function create(string $testFrameworkExecutable, string $tmpDir, string $testFrameworkConfigPath, ?string $testFrameworkConfigDir, string $jUnitFilePath, string $projectDir, array $sourceDirectories, bool $skipCoverage): TestFrameworkAdapter
    {
        return new DummyTestFrameworkAdapter();
    }

    public static function getAdapterName(): string
    {
        return 'dummy';
    }

    public static function getExecutableName(): string
    {
        return 'dummy';
    }
}
