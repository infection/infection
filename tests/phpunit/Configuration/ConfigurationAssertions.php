<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use Generator;
use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Tests\Configuration\Entry\BadgeAssertions;
use Infection\Tests\Configuration\Entry\LogsAssertions;
use Infection\Tests\Configuration\Entry\PhpUnitAssertions;
use Infection\Tests\Configuration\Entry\SourceAssertions;
use PHPUnit\Framework\TestCase;

trait ConfigurationAssertions
{
    use LogsAssertions;
    use PhpUnitAssertions;
    use SourceAssertions;

    private function assertConfigurationStateIs(
        Configuration $configuration,
        ?int $expectedTimeout,
        Source $expectedSource,
        Logs $expectedLogs,
        string $expectedLogVerbosity,
        ?string $expectedTmpDir,
        PhpUnit $expectedPhpUnit,
        array $expectedMutators,
        ?string $expectedTestFramework,
        ?string $expectedBootstrap,
        ?string $expectedInitialTestsPhpOptions,
        ?string $expectedTestFrameworkOptions,
        ?string $expectedExistingCoveragePath,
        bool $expectedDebug,
        bool $expectedOnlyCovered,
        string $expectedFormatter,
        bool $expectedNoProgress,
        bool $expectedIgnoreMsiWithNoMutations,
        ?float $expectedMinMsi,
        bool $expectedShowMutations,
        ?float $expectedMinCoveredMsi,
        ?string $expectedStringMutators
    ): void {
        $this->assertSame($expectedTimeout, $configuration->getProcessTimeout());
        $this->assertSourceStateIs(
            $configuration->getSource(),
            $expectedSource->getDirectories(),
            $expectedSource->getExcludes()
        );
        $this->assertLogsStateIs(
            $configuration->getLogs(),
            $expectedLogs->getTextLogFilePath(),
            $expectedLogs->getSummaryLogFilePath(),
            $expectedLogs->getDebugLogFilePath(),
            $expectedLogs->getPerMutatorFilePath(),
            $expectedLogs->getBadge()
        );
        $this->assertSame($expectedLogVerbosity, $configuration->getLogVerbosity());
        $this->assertSame($expectedTmpDir, $configuration->getTmpDir());
        $this->assertPhpUnitStateIs(
            $configuration->getPhpUnit(),
            $expectedPhpUnit->getConfigDir(),
            $expectedPhpUnit->getCustomPath()
        );
        $this->assertSame($expectedMutators, $configuration->getMutators());
        $this->assertSame($expectedTestFramework, $configuration->getTestFramework());
        $this->assertSame($expectedBootstrap, $configuration->getBootstrap());
        $this->assertSame($expectedInitialTestsPhpOptions, $configuration->getInitialTestsPhpOptions());
        $this->assertSame($expectedTestFrameworkOptions, $configuration->getTestFrameworkOptions());
        $this->assertSame($expectedExistingCoveragePath, $configuration->getExistingCoveragePath());
        $this->assertSame($expectedDebug, $configuration->isDebugEnabled());
        $this->assertSame($expectedOnlyCovered, $configuration->mutateOnlyCoveredCode());
        $this->assertSame($expectedFormatter, $configuration->getFormatter());
        $this->assertSame($expectedNoProgress, $configuration->showProgress());
        $this->assertSame($expectedIgnoreMsiWithNoMutations, $configuration->ignoreMsiWithNoMutations());
        $this->assertSame($expectedMinMsi, $configuration->getMinMsi());
        $this->assertSame($expectedShowMutations, $configuration->showMutations());
        $this->assertSame($expectedMinCoveredMsi, $configuration->getMinCoveredMsi());
        $this->assertSame($expectedStringMutators, $configuration->getStringMutators());
    }
}
