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

use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Mutator;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use Infection\Tests\Fixtures\Mutator\FakeMutator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    use ConfigurationAssertions;

    /**
     * @param string[] $sourceDirectories
     * @param string[] $sourceFilesExcludes
     * @param SplFileInfo[] $sourceFiles
     * @param Mutator[] $mutators
     * @param array<string, array<int, string>> $ignoreSourceCodeMutatorsMap
     */
    #[DataProvider('valueProvider')]
    public function test_it_can_be_instantiated(
        float $timeout,
        array $sourceDirectories,
        array $sourceFiles,
        string $sourceFileFilter,
        array $sourceFilesExcludes,
        Logs $logs,
        string $logVerbosity,
        string $tmpDir,
        PhpUnit $phpUnit,
        array $mutators,
        string $testFramework,
        ?string $bootstrap,
        ?string $initialTestsPhpOptions,
        string $testFrameworkExtraOptions,
        string $coveragePath,
        bool $skipCoverage,
        bool $skipInitialTests,
        bool $debug,
        bool $onlyCovered,
        bool $noProgress,
        bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        bool $showMutations,
        ?float $minCoveredMsi,
        int $msiPrecision,
        int $threadsCount,
        bool $dryRun,
        array $ignoreSourceCodeMutatorsMap,
        bool $executeOnlyCoveringTestCases,
        bool $isForGitDiffLines,
        ?string $gitDiffBase,
        ?string $mapSourceClassToTest,
    ): void {
        $config = new Configuration(
            $timeout,
            $sourceDirectories,
            $sourceFiles,
            $sourceFileFilter,
            $sourceFilesExcludes,
            $logs,
            $logVerbosity,
            $tmpDir,
            $phpUnit,
            $mutators,
            $testFramework,
            $bootstrap,
            $initialTestsPhpOptions,
            $testFrameworkExtraOptions,
            $coveragePath,
            $skipCoverage,
            $skipInitialTests,
            $debug,
            $onlyCovered,
            $noProgress,
            $ignoreMsiWithNoMutations,
            $minMsi,
            $showMutations,
            $minCoveredMsi,
            $msiPrecision,
            $threadsCount,
            $dryRun,
            $ignoreSourceCodeMutatorsMap,
            $executeOnlyCoveringTestCases,
            $isForGitDiffLines,
            $gitDiffBase,
            $mapSourceClassToTest,
        );

        $this->assertConfigurationStateIs(
            $config,
            $timeout,
            $sourceDirectories,
            $sourceFiles,
            $sourceFileFilter,
            $sourceFilesExcludes,
            $logs,
            $logVerbosity,
            $tmpDir,
            $phpUnit,
            $mutators,
            $testFramework,
            $bootstrap,
            $initialTestsPhpOptions,
            $testFrameworkExtraOptions,
            $coveragePath,
            $skipCoverage,
            $skipInitialTests,
            $debug,
            $onlyCovered,
            $noProgress,
            $ignoreMsiWithNoMutations,
            $minMsi,
            $showMutations,
            $minCoveredMsi,
            $msiPrecision,
            $threadsCount,
            $dryRun,
            $ignoreSourceCodeMutatorsMap,
            $executeOnlyCoveringTestCases,
            $isForGitDiffLines,
            $gitDiffBase,
            $mapSourceClassToTest,
        );
    }

    public static function valueProvider(): iterable
    {
        yield 'empty' => [
            10.,
            [],
            [],
            '',
            [],
            Logs::createEmpty(),
            'none',
            '',
            new PhpUnit(null, null),
            [],
            'phpunit',
            null,
            null,
            '',
            '',
            false,
            false,
            false,
            false,
            false,
            false,
            null,
            false,
            null,
            2,
            0,
            false,
            [],
            false,
            false,
            'master',
            null,
        ];

        yield 'nominal' => [
            1.,
            ['src', 'lib'],
            [
                new SplFileInfo('Foo.php', 'Foo.php', 'Foo.php'),
                new SplFileInfo('Bar.php', 'Bar.php', 'Bar.php'),
            ],
            'src/Foo.php,src/Bar.php',
            ['exclude-dir'],
            new Logs(
                'text.log',
                'report.html',
                'summary.log',
                'json.log',
                'gitlab.log',
                'debug.log',
                'mutator.log',
                true,
                StrykerConfig::forBadge('master'),
                'summary.json',
            ),
            'default',
            'custom-dir',
            new PhpUnit('dist/phpunit', 'bin/phpunit'),
            [
                'Fake' => new IgnoreMutator(new IgnoreConfig([]), new FakeMutator()),
            ],
            'phpunit',
            'bin/bootstrap.php',
            '-d zend_extension=xdebug.so',
            '',
            'coverage/',
            true,
            false,
            true,
            true,
            true,
            true,
            43.,
            true,
            45.,
            2,
            4,
            true,
            [
                'For_' => ['.*someMethod.*'],
            ],
            true,
            false,
            'master',
            MapSourceClassToTestStrategy::SIMPLE,
        ];
    }
}
