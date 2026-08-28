<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Factory;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use Webmozart\Assert\Assert;

final class ConfigurableTestFrameworkAdapterFactory implements TestFrameworkAdapterFactory
{
    public static bool $configured = false;

    private static ?TestFrameworkAdapter $adapter = null;
    private static ?string $name = null;
    private static ?string $executableName = null;

    public static function configure(
        TestFrameworkAdapter $adapter,
        string $name,
        string $executableName,
    ): void
    {
        self::assertNotConfigured();
        self::$configured = true;

        self::$adapter = $adapter;
        self::$name = $name;
        self::$executableName = $executableName;
    }

    public static function reset(): void
    {
        self::$configured = false;

        self::$adapter = null;
        self::$name = null;
        self::$executableName = null;
    }

    private static function assertNotConfigured(): void
    {
        Assert::false(
            self::$configured,
            'TestFrameworkAdapterFactory is already configured',
        );
    }

    public static function create(
        string $testFrameworkExecutable,
        string $tmpDir,
        string $testFrameworkConfigPath,
        ?string $testFrameworkConfigDir,
        string $jUnitFilePath,
        string $projectDir,
        array $sourceDirectories,
        bool $skipCoverage,
    ): TestFrameworkAdapter
    {
        return self::$adapter;
    }

    public static function getAdapterName(): string
    {
        return self::$name;
    }

    public static function getExecutableName(): string
    {
        return self::$executableName;
    }
}
