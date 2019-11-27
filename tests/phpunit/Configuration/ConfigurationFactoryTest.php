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
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Mutator\Arithmetic\AssignmentEqual;
use Infection\Mutator\Boolean\EqualIdentical;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\Mutator\Util\Mutator;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Mutator\Util\MutatorsGenerator;
use function Infection\Tests\normalizePath;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use function sys_get_temp_dir;

final class ConfigurationFactoryTest extends TestCase
{
    use ConfigurationAssertions;

    private static $mutators;

    /**
     * @var ConfigurationFactory
     */
    private $configFactory;

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        self::$mutators = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->configFactory = new ConfigurationFactory(
            new TmpDirectoryCreator(
                $this->createMock(Filesystem::class)
            )
        );
    }

    /**
     * @dataProvider valueProvider
     */
    public function test_it_can_create_a_configuration(
        SchemaConfiguration $schema,
        ?string $inputExistingCoveragePath,
        ?string $inputInitialTestsPhpOptions,
        string $inputLogVerbosity,
        bool $inputDebug,
        bool $inputOnlyCovered,
        string $inputFormatter,
        bool $inputNoProgress,
        bool $inputIgnoreMsiWithNoMutations,
        ?float $inputMinMsi,
        bool $inputShowMutations,
        ?float $inputMinCoveredMsi,
        ?string $inputMutators,
        ?string $inputTestFramework,
        ?string $inputTestFrameworkOptions,
        int $expectedTimeout,
        Source $expectedSource,
        Logs $expectedLogs,
        ?string $expectedLogVerbosity,
        string $expectedTmpDir,
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
        ?float $expectedMinCoveredMsi
    ): void {
        $config = $this->configFactory->create(
            $schema,
            $inputExistingCoveragePath,
            $inputInitialTestsPhpOptions,
            $inputLogVerbosity,
            $inputDebug,
            $inputOnlyCovered,
            $inputFormatter,
            $inputNoProgress,
            $inputIgnoreMsiWithNoMutations,
            $inputMinMsi,
            $inputShowMutations,
            $inputMinCoveredMsi,
            $inputMutators,
            $inputTestFramework,
            $inputTestFrameworkOptions
        );

        $this->assertConfigurationStateIs(
            $config,
            $expectedTimeout,
            $expectedSource,
            $expectedLogs,
            $expectedLogVerbosity,
            normalizePath($expectedTmpDir),
            $expectedPhpUnit,
            $expectedMutators,
            $expectedTestFramework,
            $expectedBootstrap,
            $expectedInitialTestsPhpOptions,
            $expectedTestFrameworkOptions,
            $expectedExistingCoveragePath,
            $expectedDebug,
            $expectedOnlyCovered,
            $expectedFormatter,
            $expectedNoProgress,
            $expectedIgnoreMsiWithNoMutations,
            $expectedMinMsi,
            $expectedShowMutations,
            $expectedMinCoveredMsi
        );
    }

    public function valueProvider(): Generator
    {
        yield 'minimal' => [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null
                ),
                '',
                new PhpUnit(null, null),
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            'none',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
            null,
            null,
            null,
            10,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            null,
            null,
            null,
            null,
            null,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];

        yield 'null timeout' => self::createValueForTimeout(
            null,
            10
        );

        yield 'config timeout' => self::createValueForTimeout(
            20,
            20
        );

        yield 'null tmp dir' => self::createValueForTmpDir(
            null,
            sys_get_temp_dir() . '/infection'
        );

        yield 'empty tmp dir' => self::createValueForTmpDir(
            '',
            sys_get_temp_dir() . '/infection'
        );

        yield 'relative tmp dir path' => self::createValueForTmpDir(
            'relative/path/to/tmp',
            '/path/to/relative/path/to/tmp/infection'
        );

        yield 'absolute tmp dir path' => self::createValueForTmpDir(
            '/absolute/path/to/tmp',
            '/absolute/path/to/tmp/infection'
        );

        yield 'no PHPUnit config dir' => self::createValueForPhpUnitConfigDir(
            'relative/path/to/phpunit/config',
            '/path/to/relative/path/to/phpunit/config'
        );

        yield 'relative PHPUnit config dir' => self::createValueForPhpUnitConfigDir(
            'relative/path/to/phpunit/config',
            '/path/to/relative/path/to/phpunit/config'
        );

        yield 'absolute PHPUnit config dir' => self::createValueForPhpUnitConfigDir(
            '/path/to/phpunit/config',
            '/path/to/phpunit/config'
        );

        yield 'no test framework' => self::createValueForTestFramework(
            null,
            null,
            null
        );

        yield 'test framework from config' => self::createValueForTestFramework(
            'phpunit',
            null,
            'phpunit'
        );

        yield 'test framework from input' => self::createValueForTestFramework(
            null,
            'phpspec',
            'phpspec'
        );

        yield 'test framework from config & input' => self::createValueForTestFramework(
            'phpunit',
            'phpspec',
            'phpspec'
        );

        yield 'test no test PHP options' => self::createValueForInitialTestsPhpOptions(
            null,
            null,
            null
        );

        yield 'test test PHP options from config' => self::createValueForInitialTestsPhpOptions(
            '-d zend_extension=xdebug.so',
            null,
            '-d zend_extension=xdebug.so'
        );

        yield 'test test PHP options from input' => self::createValueForInitialTestsPhpOptions(
            null,
            '-d zend_extension=xdebug.so',
            '-d zend_extension=xdebug.so'
        );

        yield 'test test PHP options from config & input' => self::createValueForInitialTestsPhpOptions(
            '-d zend_extension=another_xdebug.so',
            '-d zend_extension=xdebug.so',
            '-d zend_extension=xdebug.so'
        );

        yield 'test no framework PHP options' => self::createValueForInitialTestsFrameworkOptions(
            null,
            null,
            null
        );

        yield 'test framework PHP options from config' => self::createValueForInitialTestsFrameworkOptions(
            '--debug',
            null,
            '--debug'
        );

        yield 'test framework PHP options from input' => self::createValueForInitialTestsFrameworkOptions(
            null,
            '--debug',
            '--debug'
        );

        yield 'test framework PHP options from config & input' => self::createValueForInitialTestsFrameworkOptions(
            '--stop-on-failure',
            '--debug',
            '--debug'
        );

        yield 'no mutator' => self::createValueForMutators(
            [],
            null,
            self::getDefaultMutators()
        );

        yield 'mutators from config' => self::createValueForMutators(
            [
                '@default' => false,
                'MethodCallRemoval' => (object) [
                    'ignore' => [
                        'Infection\Finder\SourceFilesFinder::__construct::63',
                    ],
                ],
            ],
            null,
            [
                'MethodCallRemoval' => new MethodCallRemoval(new MutatorConfig([
                    'ignore' => [
                        'Infection\Finder\SourceFilesFinder::__construct::63',
                    ],
                ])),
            ]
        );

        yield 'mutators from config & input' => self::createValueForMutators(
            [
                '@default' => true,
                'MethodCallRemoval' => (object) [
                    'ignore' => [
                        'Infection\Finder\SourceFilesFinder::__construct::63',
                    ],
                ],
            ],
            'AssignmentEqual,EqualIdentical',
            [
                'AssignmentEqual' => new AssignmentEqual(new MutatorConfig([])),
                'EqualIdentical' => new EqualIdentical(new MutatorConfig([])),
            ]
        );

        yield 'complete' => [
            new SchemaConfiguration(
                '/path/to/infection.json',
                10,
                new Source(['src/'], ['vendor/']),
                new Logs(
                    'text.log',
                    'summary.log',
                    'debug.log',
                    'mutator.log',
                    new Badge('master')
                ),
                'config/tmp',
                new PhpUnit(
                    'config/phpunit-dir',
                    'config/phpunit'
                ),
                ['@default' => true],
                'phpunit',
                'config/bootstrap.php',
                '-d zend_extension=wrong_xdebug.so',
                '--debug'
            ),
            'dist/coverage',
            '-d zend_extension=xdebug.so',
            'none',
            true,
            true,
            'dot',
            true,
            true,
            72.3,
            true,
            81.5,
            'TrueValue',
            'phpspec',
            '--stop-on-failure',
            10,
            new Source(['src/'], ['vendor/']),
            new Logs(
                'text.log',
                'summary.log',
                'debug.log',
                'mutator.log',
                new Badge('master')
            ),
            'none',
            '/path/to/config/tmp/infection',
            new PhpUnit(
                '/path/to/config/phpunit-dir',
                'config/phpunit'
            ),
            [
                'TrueValue' => new TrueValue(new MutatorConfig([])),
            ],
            'phpspec',
            'config/bootstrap.php',
            '-d zend_extension=xdebug.so',
            '--stop-on-failure',
            'dist/coverage',
            true,
            true,
            'dot',
            true,
            true,
            72.3,
            true,
            81.5,
        ];
    }

    private static function createValueForTimeout(
        ?int $schemaTimeout,
        int $expectedTimeOut
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                $schemaTimeout,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null
                ),
                '',
                new PhpUnit(null, null),
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            'none',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
            null,
            null,
            null,
            $expectedTimeOut,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            null,
            null,
            null,
            null,
            null,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];
    }

    private static function createValueForTmpDir(
        ?string $configTmpDir,
        ?string $expectedTmpDir
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null
                ),
                $configTmpDir,
                new PhpUnit(null, null),
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            'none',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
            null,
            null,
            null,
            10,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            $expectedTmpDir,
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            null,
            null,
            null,
            null,
            null,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];
    }

    private static function createValueForPhpUnitConfigDir(
        ?string $phpUnitConfigDir,
        ?string $expectedPhpUnitConfigDir
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null
                ),
                '',
                new PhpUnit($phpUnitConfigDir, null),
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            'none',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
            null,
            null,
            null,
            10,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit($expectedPhpUnitConfigDir, null),
            self::getDefaultMutators(),
            null,
            null,
            null,
            null,
            null,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];
    }

    private static function createValueForTestFramework(
        ?string $configTestFramework,
        ?string $inputTestFramework,
        ?string $expectedTestFramework
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null
                ),
                '',
                new PhpUnit(null, null),
                [],
                $configTestFramework,
                null,
                null,
                null
            ),
            null,
            null,
            'none',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
            null,
            $inputTestFramework,
            null,
            10,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            $expectedTestFramework,
            null,
            null,
            null,
            null,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];
    }

    private static function createValueForInitialTestsPhpOptions(
        ?string $configInitialTestsPhpOptions,
        ?string $inputInitialTestsPhpOptions,
        ?string $expectedInitialTestPhpOptions
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null
                ),
                '',
                new PhpUnit(null, null),
                [],
                null,
                null,
                $configInitialTestsPhpOptions,
                null
            ),
            null,
            $inputInitialTestsPhpOptions,
            'none',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
            null,
            null,
            null,
            10,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            null,
            null,
            $expectedInitialTestPhpOptions,
            null,
            null,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];
    }

    private static function createValueForInitialTestsFrameworkOptions(
        ?string $configInitialTestsFrameworkOptions,
        ?string $inputInitialTestsFrameworkOptions,
        ?string $expectedInitialTestFrameworkOptions
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null
                ),
                '',
                new PhpUnit(null, null),
                [],
                null,
                null,
                null,
                $configInitialTestsFrameworkOptions
            ),
            null,
            null,
            'none',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
            null,
            null,
            $inputInitialTestsFrameworkOptions,
            10,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            null,
            null,
            null,
            $expectedInitialTestFrameworkOptions,
            null,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];
    }

    /**
     * @param array<string,Mutator> $expectedMutators
     */
    private static function createValueForMutators(
        array $configMutators,
        ?string $inputMutators,
        array $expectedMutators
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null
                ),
                null,
                new PhpUnit(null, null),
                $configMutators,
                null,
                null,
                null,
                null
            ),
            null,
            null,
            'none',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
            $inputMutators,
            null,
            null,
            10,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            $expectedMutators,
            null,
            null,
            null,
            null,
            null,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];
    }

    /**
     * @return array<string, Mutator>
     */
    private static function getDefaultMutators(): array
    {
        if (null === self::$mutators) {
            self::$mutators = (new MutatorsGenerator([]))->generate();
        }

        return self::$mutators;
    }
}
