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

use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use PHPUnit\Framework\TestCase;

final class SchemaConfigurationTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function test_it_can_be_instantiated(
        string $path,
        ?float $timeout,
        Source $source,
        Logs $logs,
        ?string $tmpDir,
        PhpUnit $phpUnit,
        ?bool $ignoreMsiWithNoMutations,
        $minMsi,
        $minCoveredMsi,
        array $mutators,
        ?string $testFramework,
        ?string $bootstrap,
        ?string $initialTestsPhpOptions,
        ?string $testFrameworkExtraOptions
    ): void {
        $config = new SchemaConfiguration(
            $path,
            $timeout,
            $source,
            $logs,
            $tmpDir,
            $phpUnit,
            $ignoreMsiWithNoMutations,
            $minMsi,
            $minCoveredMsi,
            $mutators,
            $testFramework,
            $bootstrap,
            $initialTestsPhpOptions,
            $testFrameworkExtraOptions
        );

        $this->assertSame($path, $config->getFile());
        $this->assertSame($timeout, $config->getTimeout());
        $this->assertSame($source, $config->getSource());
        $this->assertSame($logs, $config->getLogs());
        $this->assertSame($tmpDir, $config->getTmpDir());
        $this->assertSame($phpUnit, $config->getPhpUnit());
        $this->assertSame($ignoreMsiWithNoMutations, $config->getIgnoreMsiWithNoMutations());
        $this->assertSame($minMsi, $config->getMinMsi());
        $this->assertSame($minCoveredMsi, $config->getMinCoveredMsi());
        $this->assertSame($mutators, $config->getMutators());
        $this->assertSame($testFramework, $config->getTestFramework());
        $this->assertSame($bootstrap, $config->getBootstrap());
        $this->assertSame($initialTestsPhpOptions, $config->getInitialTestsPhpOptions());
        $this->assertSame($testFrameworkExtraOptions, $config->getTestFrameworkExtraOptions());
    }

    public function valueProvider(): iterable
    {
        yield 'minimal' => [
            '',
            null,
            new Source([], []),
            Logs::createEmpty(),
            null,
            new PhpUnit(null, null),
            null,
            null,
            null,
            [],
            null,
            null,
            null,
            null,
        ];

        yield 'complete' => [
            '/path/to/config',
            10.,
            new Source(['src', 'lib'], ['fixtures', 'tests']),
            new Logs(
                'text.log',
                'summary.log',
                'json.log',
                'debug.log',
                'mutator.log',
                'checkstyle.xml',
                new Badge('master')
            ),
            'path/to/tmp',
            new PhpUnit('dist/phpunit', 'bin/phpunit'),
            true,
            12.0,
            35.0,
            [
                '@arithmetic' => true,
                '@cast' => false,
            ],
            'phpunit',
            'bin/bootstrap.php',
            '-d zend_extension=xdebug.so',
            '--debug',
        ];
    }
}
