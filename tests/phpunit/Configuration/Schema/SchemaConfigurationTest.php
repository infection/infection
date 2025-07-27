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

namespace Infection\Tests\Configuration\Schema;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Configuration\Schema\SchemaConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaConfiguration::class)]
final class SchemaConfigurationTest extends TestCase
{
    #[DataProvider('valueProvider')]
    public function test_it_can_be_instantiated(
        string $path = '',
        ?float $timeout = null,
        Source $source = new Source([], []),
        ?Logs $logs = null,
        ?string $tmpDir = null,
        PhpUnit $phpUnit = new PhpUnit(null, null),
        PhpStan $phpStan = new PhpStan(null, null),
        ?bool $ignoreMsiWithNoMutations = null,
        mixed $minMsi = null,
        mixed $minCoveredMsi = null,
        array $mutators = [],
        ?string $testFramework = null,
        ?string $bootstrap = null,
        ?string $initialTestsPhpOptions = null,
        ?string $testFrameworkExtraOptions = null,
        string|int|null $threadCount = null,
        ?string $staticAnalysisTool = null,
    ): void {
        $logs ??= Logs::createEmpty();
        $config = new SchemaConfiguration(
            $path,
            $timeout,
            $source,
            $logs,
            $tmpDir,
            $phpUnit,
            $phpStan,
            $ignoreMsiWithNoMutations,
            $minMsi,
            $minCoveredMsi,
            $mutators,
            $testFramework,
            $bootstrap,
            $initialTestsPhpOptions,
            $testFrameworkExtraOptions,
            $threadCount,
            $staticAnalysisTool,
        );

        $this->assertSame($path, $config->getFile(), 'Failed path check');
        $this->assertSame($timeout, $config->getTimeout(), 'Failed timeout check');
        $this->assertSame($source, $config->getSource(), 'Failed source check');
        $this->assertSame($logs, $config->getLogs(), 'Failed logs check');
        $this->assertSame($tmpDir, $config->getTmpDir(), 'Failed tmpDir check');
        $this->assertSame($phpUnit, $config->getPhpUnit(), 'Failed phpUnit check');
        $this->assertSame($phpStan, $config->getPhpStan(), 'Failed phpStan check');
        $this->assertSame($ignoreMsiWithNoMutations, $config->getIgnoreMsiWithNoMutations(), 'Failed ignoreMsiWithNoMutations check');
        $this->assertSame($minMsi, $config->getMinUncoveredMsi(), 'Failed minMsi check');
        $this->assertSame($minCoveredMsi, $config->getMinCoveredMsi(), 'Failed minCoveredMsi check');
        $this->assertSame($mutators, $config->getMutators(), 'Failed mutators check');
        $this->assertSame($testFramework, $config->getTestFramework(), 'Failed testFramework check');
        $this->assertSame($bootstrap, $config->getBootstrap(), 'Failed bootstrap check');
        $this->assertSame($initialTestsPhpOptions, $config->getInitialTestsPhpOptions(), 'Failed initialTestsPhpOptions check');
        $this->assertSame($testFrameworkExtraOptions, $config->getTestFrameworkExtraOptions(), 'Failed testFrameworkExtraOptions check');
        $this->assertSame($threadCount, $config->getThreads(), 'Failed threadCount check');
        $this->assertSame($staticAnalysisTool, $config->getStaticAnalysisTool(), 'Failed staticAnalysisTool check');
    }

    public static function valueProvider(): iterable
    {
        yield 'minimal' => [];

        yield 'complete' => [
            'path' => '/path/to/config',
            'timeout' => 10.,
            'source' => new Source(['src', 'lib'], ['fixtures', 'tests']),
            'logs' => new Logs(
                'text.log',
                'report.html',
                'summary.log',
                'json.log',
                'gitlab.log',
                'debug.log',
                'mutator.log',
                true,
                StrykerConfig::forFullReport('master'),
                'summary.json',
            ),
            'tmpDir' => 'path/to/tmp',
            'phpUnit' => new PhpUnit('dist/phpunit', 'bin/phpunit'),
            'phpStan' => new PhpStan('bin/phpstan-config-dir', 'bin/phpstan'),
            'ignoreMsiWithNoMutations' => true,
            'minMsi' => 12.0,
            'minCoveredMsi' => 35.0,
            'mutators' => [
                '@arithmetic' => true,
                '@cast' => false,
            ],
            'testFramework' => 'phpunit',
            'bootstrap' => 'bin/bootstrap.php',
            'initialTestsPhpOptions' => '-d zend_extension=xdebug.so',
            'testFrameworkExtraOptions' => '--debug',
            'threadCount' => 'max',
            'staticAnalysisTool' => 'phpstan',
        ];
    }
}
