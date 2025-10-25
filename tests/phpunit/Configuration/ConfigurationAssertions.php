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
        $this->assertSame($expectedTimeout, $configuration->getProcessTimeout(), 'Failed timeout check');
        $this->assertSame($expectedSourceDirectories, $configuration->getSourceDirectories(), 'Failed sourceDirectories check');
        $this->assertSame(
            self::normalizePaths($expectedSourceFiles),
            self::normalizePaths($configuration->getSourceFiles()),
            'Failed sourceFiles check',
        );
        $this->assertSame($expectedFilter, $configuration->getSourceFilesFilter(), 'Failed sourceFilesFilter check');
        $this->assertSame($expectedSourceFilesExcludes, $configuration->getSourceFilesExcludes(), 'Failed sourceFilesExcludes check');
        $this->assertEquals(
            $expectedLogs,
            $configuration->getLogs(),
        );
        $this->assertSame($expectedLogVerbosity, $configuration->getLogVerbosity(), 'Failed logVerbosity check');
        $this->assertSame($expectedTmpDir, $configuration->getTmpDir(), 'Failed tmpDir check');
        $this->assertPhpUnitStateIs(
            $configuration->getPhpUnit(),
            $expectedPhpUnit->getConfigDir(),
            $expectedPhpUnit->getCustomPath(),
        );
        $this->assertSame($expectedPhpStan->getConfigDir(), $configuration->getPhpStan()->getConfigDir(), 'Failed PHPStan config dir check');
        $this->assertSame($expectedPhpStan->getCustomPath(), $configuration->getPhpStan()->getCustomPath(), 'Failed PHPStan custom path check');
        $this->assertEqualsWithDelta($expectedMutators, $configuration->getMutators(), 10., 'Failed mutators check');
        $this->assertSame($expectedTestFramework, $configuration->getTestFramework(), 'Failed testFramework check');
        $this->assertSame($expectedBootstrap, $configuration->getBootstrap(), 'Failed bootstrap check');
        $this->assertSame($expectedInitialTestsPhpOptions, $configuration->getInitialTestsPhpOptions(), 'Failed initialTestsPhpOptions check');
        $this->assertSame(
            $expectedTestFrameworkExtraOptions,
            $configuration->getTestFrameworkExtraOptions(),
            'Failed testFrameworkExtraOptions check',
        );
        $this->assertSame(
            $this->parseStaticAnalysisToolOptionsForAssertion($expectedStaticAnalysisToolOptions),
            $configuration->getStaticAnalysisToolOptions(),
            'Failed staticAnalysisToolOptions check',
        );
        $this->assertSame($expectedCoveragePath, $configuration->getCoveragePath(), 'Failed coveragePath check');
        $this->assertSame($expectedSkipCoverage, $configuration->shouldSkipCoverage(), 'Failed skipCoverage check');
        $this->assertSame($expectedSkipInitialTests, $configuration->shouldSkipInitialTests(), 'Failed skipInitialTests check');
        $this->assertSame($expectedDebug, $configuration->isDebugEnabled(), 'Failed isDebugEnabled check');
        $this->assertSame($expectedWithUncovered, !$configuration->mutateOnlyCoveredCode(), 'Failed onlyCoveredCode check');
        $this->assertSame($expectedNoProgress, $configuration->noProgress(), 'Failed noProgress check');
        $this->assertSame($expectedIgnoreMsiWithNoMutations, $configuration->ignoreMsiWithNoMutations(), 'Failed ignoreMsiWithNoMutations check');
        $this->assertSame($expectedMinMsi, $configuration->getMinMsi(), 'Failed minMsi check');
        $this->assertSame($expectedNumberOfShownMutations, $configuration->getNumberOfShownMutations(), 'Failed numberOfShownMutations check');
        $this->assertSame($expectedMinCoveredMsi, $configuration->getMinCoveredMsi(), 'Failed minCoveredMsi check');
        $this->assertSame($expectedMsiPrecision, $configuration->getMsiPrecision(), 'Failed msiPrecision check');
        $this->assertSame($expectedThreadCount, $configuration->getThreadCount(), 'Failed threadsCount check');
        $this->assertSame($expectedDryRyn, $configuration->isDryRun(), 'Failed dryRun check');
        $this->assertSame($expectedIgnoreSourceCodeMutatorsMap, $configuration->getIgnoreSourceCodeMutatorsMap(), 'Failed ignoreSourceCodeMutatorsMap check');
        $this->assertSame($expectedExecuteOnlyCoveringTestCases, $configuration->getExecuteOnlyCoveringTestCases(), 'Failed executeOnlyCoveringTestCases check');
        $this->assertSame($expectedIsForGitDiffLines, $configuration->isForGitDiffLines(), 'Failed isForGitDiffLines check');
        $this->assertSame($expectedGitDiffBase, $configuration->getGitDiffBase(), 'Failed gitDiffBase check');
        $this->assertSame($expectedMapSourceClassToTest, $configuration->getMapSourceClassToTestStrategy(), 'Failed mapSourceClassToTestStrategy check');
        $this->assertSame($expectedLoggerProjectRootDirectory, $configuration->getLoggerProjectRootDirectory(), 'Failed loggerProjectRootDirectory check');
        $this->assertSame($expectedStaticAnalysisTool, $configuration->getStaticAnalysisTool(), 'Failed staticAnalysisTool check');
        $this->assertSame($expectedMutantId, $configuration->getMutantId(), 'Failed mutantId check');
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
