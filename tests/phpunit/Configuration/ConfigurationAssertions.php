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
use function explode;
use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Tests\Configuration\Entry\LogsAssertions;
use Infection\Tests\Configuration\Entry\PhpUnitAssertions;
use function ltrim;
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
        PhpStan $expectedPhpStan,
        array $expectedMutators,
        string $expectedTestFramework,
        ?string $expectedBootstrap,
        ?string $expectedInitialTestsPhpOptions,
        string $expectedTestFrameworkExtraOptions,
        ?string $expectedStaticAnalysisToolOptions,
        string $expectedCoveragePath,
        bool $expectedSkipCoverage,
        bool $expectedSkipInitialTests,
        bool $expectedDebug,
        bool $expectedWithUncovered,
        bool $expectedNoProgress,
        bool $expectedIgnoreMsiWithNoMutations,
        ?float $expectedMinMsi,
        ?int $expectedNumberOfShownMutations,
        ?float $expectedMinCoveredMsi,
        int $expectedMsiPrecision,
        int $expectedThreadCount,
        bool $expectedDryRyn,
        array $expectedIgnoreSourceCodeMutatorsMap,
        bool $expectedExecuteOnlyCoveringTestCases,
        bool $expectedIsForGitDiffLines,
        ?string $expectedGitDiffBase,
        ?string $expectedMapSourceClassToTest,
        ?string $expectedLoggerProjectRootDirectory,
        ?string $expectedStaticAnalysisTool,
        ?string $expectedMutantId,
    ): void {
        $this->assertSame($expectedTimeout, $configuration->processTimeout, 'Failed timeout check');
        $this->assertSame($expectedSourceDirectories, $configuration->sourceDirectories, 'Failed sourceDirectories check');
        $this->assertSame(
            self::normalizePaths($expectedSourceFiles),
            self::normalizePaths($configuration->sourceFiles),
            'Failed sourceFiles check',
        );
        $this->assertSame($expectedFilter, $configuration->sourceFilesFilter, 'Failed sourceFilesFilter check');
        $this->assertSame($expectedSourceFilesExcludes, $configuration->sourceFilesExcludes, 'Failed sourceFilesExcludes check');
        $this->assertLogsStateIs(
            $configuration->logs,
            $expectedLogs->getTextLogFilePath(),
            $expectedLogs->getHtmlLogFilePath(),
            $expectedLogs->getSummaryLogFilePath(),
            $expectedLogs->getJsonLogFilePath(),
            $expectedLogs->getGitlabLogFilePath(),
            $expectedLogs->getDebugLogFilePath(),
            $expectedLogs->getPerMutatorFilePath(),
            $expectedLogs->getUseGitHubAnnotationsLogger(),
            $expectedLogs->getStrykerConfig(),
            $expectedLogs->getSummaryJsonLogFilePath(),
        );
        $this->assertSame($expectedLogVerbosity, $configuration->logVerbosity, 'Failed logVerbosity check');
        $this->assertSame($expectedTmpDir, $configuration->tmpDir, 'Failed tmpDir check');
        $this->assertPhpUnitStateIs(
            $configuration->phpUnit,
            $expectedPhpUnit->getConfigDir(),
            $expectedPhpUnit->getCustomPath(),
        );
        $this->assertSame($expectedPhpStan->getConfigDir(), $configuration->phpStan->getConfigDir(), 'Failed PHPStan config dir check');
        $this->assertSame($expectedPhpStan->getCustomPath(), $configuration->phpStan->getCustomPath(), 'Failed PHPStan custom path check');
        $this->assertEqualsWithDelta($expectedMutators, $configuration->mutators, 10., 'Failed mutators check');
        $this->assertSame($expectedTestFramework, $configuration->testFramework, 'Failed testFramework check');
        $this->assertSame($expectedBootstrap, $configuration->bootstrap, 'Failed bootstrap check');
        $this->assertSame($expectedInitialTestsPhpOptions, $configuration->initialTestsPhpOptions, 'Failed initialTestsPhpOptions check');
        $this->assertSame(
            $expectedTestFrameworkExtraOptions,
            $configuration->testFrameworkExtraOptions,
            'Failed testFrameworkExtraOptions check',
        );
        $this->assertSame(
            $this->parseStaticAnalysisToolOptionsForAssertion($expectedStaticAnalysisToolOptions),
            $configuration->getStaticAnalysisToolOptions(),
            'Failed staticAnalysisToolOptions check',
        );
        $this->assertSame($expectedCoveragePath, $configuration->coveragePath, 'Failed coveragePath check');
        $this->assertSame($expectedSkipCoverage, $configuration->skipCoverage, 'Failed skipCoverage check');
        $this->assertSame($expectedSkipInitialTests, $configuration->skipInitialTests, 'Failed skipInitialTests check');
        $this->assertSame($expectedDebug, $configuration->isDebugEnabled, 'Failed isDebugEnabled check');
        $this->assertSame($expectedWithUncovered, !$configuration->mutateOnlyCoveredCode(), 'Failed onlyCoveredCode check');
        $this->assertSame($expectedNoProgress, $configuration->noProgress, 'Failed noProgress check');
        $this->assertSame($expectedIgnoreMsiWithNoMutations, $configuration->ignoreMsiWithNoMutations, 'Failed ignoreMsiWithNoMutations check');
        $this->assertSame($expectedMinMsi, $configuration->minMsi, 'Failed minMsi check');
        $this->assertSame($expectedNumberOfShownMutations, $configuration->numberOfShownMutations, 'Failed numberOfShownMutations check');
        $this->assertSame($expectedMinCoveredMsi, $configuration->minCoveredMsi, 'Failed minCoveredMsi check');
        $this->assertSame($expectedMsiPrecision, $configuration->msiPrecision, 'Failed msiPrecision check');
        $this->assertSame($expectedThreadCount, $configuration->threadCount, 'Failed threadsCount check');
        $this->assertSame($expectedDryRyn, $configuration->isDryRun, 'Failed dryRun check');
        $this->assertSame($expectedIgnoreSourceCodeMutatorsMap, $configuration->ignoreSourceCodeMutatorsMap, 'Failed ignoreSourceCodeMutatorsMap check');
        $this->assertSame($expectedExecuteOnlyCoveringTestCases, $configuration->executeOnlyCoveringTestCases, 'Failed executeOnlyCoveringTestCases check');
        $this->assertSame($expectedIsForGitDiffLines, $configuration->isForGitDiffLines, 'Failed isForGitDiffLines check');
        $this->assertSame($expectedGitDiffBase, $configuration->gitDiffBase, 'Failed gitDiffBase check');
        $this->assertSame($expectedMapSourceClassToTest, $configuration->mapSourceClassToTestStrategy, 'Failed mapSourceClassToTestStrategy check');
        $this->assertSame($expectedLoggerProjectRootDirectory, $configuration->loggerProjectRootDirectory, 'Failed loggerProjectRootDirectory check');
        $this->assertSame($expectedStaticAnalysisTool, $configuration->staticAnalysisTool, 'Failed staticAnalysisTool check');
        $this->assertSame($expectedMutantId, $configuration->mutantId, 'Failed mutantId check');
    }

    /**
     * @param SplFileInfo[] $fileInfos
     *
     * @return string[]
     */
    private static function normalizePaths(array $fileInfos): array
    {
        return array_map(
            static fn (SplFileInfo $fileInfo): string => $fileInfo->getPathname(),
            $fileInfos,
        );
    }

    /**
     * @return list<string>
     */
    private function parseStaticAnalysisToolOptionsForAssertion(?string $extraOptions): array
    {
        if ($extraOptions === null || $extraOptions === '') {
            return [];
        }

        return array_map(
            static fn ($option): string => '--' . $option,
            explode(' --', ltrim($extraOptions, '-')),
        );
    }
}
