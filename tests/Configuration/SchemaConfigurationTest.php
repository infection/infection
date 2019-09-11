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
use Infection\Configuration\SchemaConfiguration;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mutator\ArrayItemRemoval;
use Infection\Configuration\Entry\Mutator\ArrayItemRemovalSettings;
use Infection\Configuration\Entry\Mutator\BCMath;
use Infection\Configuration\Entry\Mutator\BCMathSettings;
use Infection\Configuration\Entry\Mutator\MBString;
use Infection\Configuration\Entry\Mutator\MBStringSettings;
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\Mutator\TrueValue;
use Infection\Configuration\Entry\Mutator\TrueValueSettings;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use PHPUnit\Framework\TestCase;

class SchemaConfigurationTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function test_it_can_be_instantiated(
        ?int $timeout,
        Source $source,
        Logs $logs,
        ?string $tmpDir,
        PhpUnit $phpUnit,
        Mutators $mutators,
        ?string $testFramework,
        ?string $bootstrap,
        ?string $initialTestsPhpOptions,
        ?string $testFrameworkOptions
    ): void {
        $config = new SchemaConfiguration(
            $timeout,
            $source,
            $logs,
            $tmpDir,
            $phpUnit,
            $mutators,
            $testFramework,
            $bootstrap,
            $initialTestsPhpOptions,
            $testFrameworkOptions
        );

        $this->assertSame($timeout, $config->getTimeout());
        $this->assertSame($source, $config->getSource());
        $this->assertSame($logs, $config->getLogs());
        $this->assertSame($tmpDir, $config->getTmpDir());
        $this->assertSame($phpUnit, $config->getPhpUnit());
        $this->assertSame($mutators, $config->getMutators());
        $this->assertSame($testFramework, $config->getTestFramework());
        $this->assertSame($bootstrap, $config->getBootstrap());
        $this->assertSame($initialTestsPhpOptions, $config->getInitialTestsPhpOptions());
        $this->assertSame($testFrameworkOptions, $config->getTestFrameworkOptions());
    }

    public function valueProvider(): Generator
    {
        foreach ($this->provideNullableStrictlyPositiveInteger() as $timeout) {
            foreach ($this->provideSource() as $source) {
                foreach ($this->provideLogs() as $log) {
                    foreach ([null, 'custom-dir'] as $tmpDir) {
                        foreach ($this->providePhpUnit() as $phpUnit) {
                            foreach ($this->provideMutators() as $mutators) {
                                foreach ([null, 'phpunit'] as $testFramework) {
                                    foreach ([null, 'bin/bootstrap.php'] as $bootstrap) {
                                        foreach ([null, '-d zend_extension=xdebug.so'] as $initialTestOptions) {
                                            foreach ([null, '--debug'] as $testFrameworkOptions) {
                                                yield [
                                                    $timeout,
                                                    $source,
                                                    $log,
                                                    $tmpDir,
                                                    $phpUnit,
                                                    $mutators,
                                                    $testFramework,
                                                    $bootstrap,
                                                    $initialTestOptions,
                                                    $testFrameworkOptions,
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function provideNullableStrictlyPositiveInteger(): Generator
    {
        yield null;

        yield 10;
    }

    public function provideSource(): Generator
    {
        yield new Source([], []);

        yield new Source(['src', 'lib'], ['fixtures', 'tests']);
    }

    public function provideLogs(): Generator
    {
        yield new Logs(
            null,
            null,
            null,
            null,
            null
        );

        yield new Logs(
            'text.log',
            'summary.log',
            'debug.log',
            'mutator.log',
            new Badge('master')
        );
    }

    public function providePhpUnit(): Generator
    {
        yield new PhpUnit(null, null);

        yield new PhpUnit('dist/phpunit', 'bin/phpunit');
    }

    public function provideMutators(): Generator
    {
        yield new Mutators(
            [],
            null,
            null,
            null,
            null
        );

        yield new Mutators(
            [
                '@arithmetic' => true,
                '@cast' => false,
            ],
            new TrueValue(
                true,
                ['fileA'],
                new TrueValueSettings(
                    false,
                    false
                )
            ),
            new ArrayItemRemoval(
                true,
                ['file'],
                new ArrayItemRemovalSettings(
                    'first',
                    10
                )
            ),
            new BCMath(
                true,
                [],
                new BCMathSettings(
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true
                )
            ),
            new MBString(
                true,
                [],
                new MBStringSettings(
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true
                )
            )
        );
    }
}
