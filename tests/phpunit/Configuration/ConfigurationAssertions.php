<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use function array_map;
use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Tests\Configuration\Entry\LogsAssertions;
use Infection\Tests\Configuration\Entry\PhpUnitAssertions;
use Symfony\Component\Finder\SplFileInfo;

trait ConfigurationAssertions
{
    use LogsAssertions;
    use PhpUnitAssertions;

    /**
     * @param string[] $expectedSourceDirectories
     * @param string[] $expectedSourceFilesExcludes
     * @param SplFileInfo[] $expectedSourceFiles
     * @param array<string, array<int, string>> $expectedIgnoreSourceCodeMutatorsMap
     */
    private function assertConfigurationStateIs(
        Configuration $configuration,
        ?float $expectedTimeout,
        array $expectedSourceDirectories,
        array $expectedSourceFiles,
        string $expectedFilter,
        array $expectedSourceFilesExcludes,
        Logs $expectedLogs,
        string $expectedLogVerbosity,
        string $expectedTmpDir,
        PhpUnit $expectedPhpUnit,
        array $expectedMutators,
        string $expectedTestFramework,
        ?string $expectedBootstrap,
        ?string $expectedInitialTestsPhpOptions,
        string $expectedTestFrameworkExtraOptions,
        string $expectedCoveragePath,
        bool $expectedSkipCoverage,
        bool $expectedSkipInitialTests,
        bool $expectedDebug,
        bool $expectedOnlyCovered,
        bool $expectedNoProgress,
        bool $expectedIgnoreMsiWithNoMutations,
        ?float $expectedMinMsi,
        bool $expectedShowMutations,
        ?float $expectedMinCoveredMsi,
        int $expectedMsiPrecision,
        int $expectedThreadCount,
        bool $expectedDryRyn,
        array $expectedIgnoreSourceCodeMutatorsMap
    ): void {
        $this->assertSame($expectedTimeout, $configuration->getProcessTimeout());
        $this->assertSame($expectedSourceDirectories, $configuration->getSourceDirectories());
        $this->assertSame(
            self::normalizePaths($expectedSourceFiles),
            self::normalizePaths($configuration->getSourceFiles())
        );
        $this->assertSame($expectedFilter, $configuration->getSourceFilesFilter());
        $this->assertSame($expectedSourceFilesExcludes, $configuration->getSourceFilesExcludes());
        $this->assertLogsStateIs(
            $configuration->getLogs(),
            $expectedLogs->getTextLogFilePath(),
            $expectedLogs->getSummaryLogFilePath(),
            $expectedLogs->getJsonLogFilePath(),
            $expectedLogs->getDebugLogFilePath(),
            $expectedLogs->getPerMutatorFilePath(),
            $expectedLogs->getCheckstyleFilePath(),
            $expectedLogs->getBadge()
        );
        $this->assertSame($expectedLogVerbosity, $configuration->getLogVerbosity());
        $this->assertSame($expectedTmpDir, $configuration->getTmpDir());
        $this->assertPhpUnitStateIs(
            $configuration->getPhpUnit(),
            $expectedPhpUnit->getConfigDir(),
            $expectedPhpUnit->getCustomPath()
        );
        $this->assertEqualsWithDelta($expectedMutators, $configuration->getMutators(), 10.);
        $this->assertSame($expectedTestFramework, $configuration->getTestFramework());
        $this->assertSame($expectedBootstrap, $configuration->getBootstrap());
        $this->assertSame($expectedInitialTestsPhpOptions, $configuration->getInitialTestsPhpOptions());
        $this->assertSame(
            $expectedTestFrameworkExtraOptions,
            $configuration->getTestFrameworkExtraOptions()
        );
        $this->assertSame($expectedCoveragePath, $configuration->getCoveragePath());
        $this->assertSame($expectedSkipCoverage, $configuration->shouldSkipCoverage());
        $this->assertSame($expectedSkipInitialTests, $configuration->shouldSkipInitialTests());
        $this->assertSame($expectedDebug, $configuration->isDebugEnabled());
        $this->assertSame($expectedOnlyCovered, $configuration->mutateOnlyCoveredCode());
        $this->assertSame($expectedNoProgress, $configuration->noProgress());
        $this->assertSame($expectedIgnoreMsiWithNoMutations, $configuration->ignoreMsiWithNoMutations());
        $this->assertSame($expectedMinMsi, $configuration->getMinMsi());
        $this->assertSame($expectedShowMutations, $configuration->showMutations());
        $this->assertSame($expectedMinCoveredMsi, $configuration->getMinCoveredMsi());
        $this->assertSame($expectedMsiPrecision, $configuration->getMsiPrecision());
        $this->assertSame($expectedThreadCount, $configuration->getThreadCount());
        $this->assertSame($expectedDryRyn, $configuration->isDryRun());
        $this->assertSame($expectedIgnoreSourceCodeMutatorsMap, $configuration->getIgnoreSourceCodeMutatorsMap());
    }

    /**
     * @param SplFileInfo[] $fileInfos
     *
     * @return string[]
     */
    private static function normalizePaths(array $fileInfos): array
    {
        return array_map(
            static function (SplFileInfo $fileInfo): string {
                return $fileInfo->getPathname();
            },
            $fileInfos
        );
    }
}
