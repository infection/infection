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

use Generator;
use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\TestFramework\TestFrameworkExtraOptions;
use Infection\Tests\Fixtures\Mutator\Fake;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkExtraOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

final class ConfigurationTest extends TestCase
{
    use ConfigurationAssertions;

    /**
     * @dataProvider valueProvider
     *
     * @param string[]      $sourceDirectories
     * @param SplFileInfo[] $sourceFiles
     */
    public function test_it_can_be_instantiated(
        ?int $timeout,
        array $sourceDirectories,
        array $sourceFiles,
        Logs $logs,
        string $logVerbosity,
        string $tmpDir,
        PhpUnit $phpUnit,
        array $mutators,
        string $testFramework,
        ?string $bootstrap,
        ?string $initialTestsPhpOptions,
        TestFrameworkExtraOptions $testFrameworkExtraOptions,
        string $existingCoveragePath,
        bool $skipCoverage,
        bool $debug,
        bool $onlyCovered,
        string $formatter,
        bool $noProgress,
        bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        bool $showMutations,
        ?float $minCoveredMsi
    ): void {
        $config = new Configuration(
            $timeout,
            $sourceDirectories,
            $sourceFiles,
            $logs,
            $logVerbosity,
            $tmpDir,
            $phpUnit,
            $mutators,
            $testFramework,
            $bootstrap,
            $initialTestsPhpOptions,
            $testFrameworkExtraOptions,
            $existingCoveragePath,
            $skipCoverage,
            $debug,
            $onlyCovered,
            $formatter,
            $noProgress,
            $ignoreMsiWithNoMutations,
            $minMsi,
            $showMutations,
            $minCoveredMsi
        );

        $this->assertConfigurationStateIs(
            $config,
            $timeout,
            $sourceDirectories,
            $sourceFiles,
            $logs,
            $logVerbosity,
            $tmpDir,
            $phpUnit,
            $mutators,
            $testFramework,
            $bootstrap,
            $initialTestsPhpOptions,
            $testFrameworkExtraOptions,
            $existingCoveragePath,
            $skipCoverage,
            $debug,
            $onlyCovered,
            $formatter,
            $noProgress,
            $ignoreMsiWithNoMutations,
            $minMsi,
            $showMutations,
            $minCoveredMsi
        );
    }

    public function valueProvider(): Generator
    {
        yield 'empty' => [
            10,
            [],
            [],
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            '',
            new PhpUnit(null, null),
            [],
            'phpunit',
            null,
            null,
            new DummyTestFrameworkExtraOptions(),
            '',
            false,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];

        yield 'nominal' => [
            1,
            ['src', 'lib'],
            [
                new SplFileInfo('Foo.php', 'Foo.php', 'Foo.php'),
                new SplFileInfo('Bar.php', 'Bar.php', 'Bar.php'),
            ],
            new Logs(
                'text.log',
                'summary.log',
                'debug.log',
                'mutator.log',
                new Badge('master')
            ),
            'default',
            'custom-dir',
            new PhpUnit('dist/phpunit', 'bin/phpunit'),
            [
                'Fake' => new Fake(),
            ],
            'phpunit',
            'bin/bootstrap.php',
            '-d zend_extension=xdebug.so',
            new DummyTestFrameworkExtraOptions(),
            'coverage/',
            true,
            true,
            true,
            'progress',
            true,
            true,
            43.,
            true,
            43.,
        ];
    }
}
