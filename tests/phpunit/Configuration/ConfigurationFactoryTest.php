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
use Infection\FileSystem\SourceFileCollector;
use Infection\FileSystem\TmpDirProvider;
use Infection\Mutator\Arithmetic\AssignmentEqual;
use Infection\Mutator\Boolean\EqualIdentical;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\Mutator\Util\Mutator;
use Infection\Mutator\Util\MutatorConfig;
use Infection\TestFramework\PhpSpec\PhpSpecExtraOptions;
use Infection\TestFramework\PhpUnit\PhpUnitExtraOptions;
use Infection\TestFramework\TestFrameworkExtraOptions;
use function Infection\Tests\normalizePath;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Finder\SplFileInfo;
use function sys_get_temp_dir;

final class ConfigurationFactoryTest extends TestCase
{
    use ConfigurationAssertions;

    private static $mutators;

    /**
     * @var ConfigurationFactory
     */
    private $configFactory;

    public static function tearDownAfterClass(): void
    {
        self::$mutators = null;
    }

    protected function setUp(): void
    {
        /** @var SourceFileCollector&ObjectProphecy $sourceFilesCollectorProphecy */
        $sourceFilesCollectorProphecy = $this->prophesize(SourceFileCollector::class);

        $sourceFilesCollectorProphecy
            ->collectFiles([], [], '')
            ->willReturn([])
        ;
        $sourceFilesCollectorProphecy
            ->collectFiles(['src/'], ['vendor/'], 'src/Foo.php, src/Bar.php')
            ->willReturn([
                new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
            ])
        ;

        $this->configFactory = new ConfigurationFactory(
            new TmpDirProvider(),
            new MutatorFactory(),
            new MutatorParser(),
            $sourceFilesCollectorProphecy->reveal()
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
        string $inputMutators,
        ?string $inputTestFramework,
        ?string $inputTestFrameworkOptions,
        string $inputFilter,
        int $expectedTimeout,
        array $expectedSourceDirectories,
        array $expectedSourceFiles,
        Logs $expectedLogs,
        ?string $expectedLogVerbosity,
        string $expectedTmpDir,
        PhpUnit $expectedPhpUnit,
        array $expectedMutators,
        string $expectedTestFramework,
        ?string $expectedBootstrap,
        ?string $expectedInitialTestsPhpOptions,
        TestFrameworkExtraOptions $expectedTestFrameworkOptions,
        string $expectedExistingCoverageBasePath,
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
            $inputTestFrameworkOptions,
            $inputFilter
        );

        $this->assertConfigurationStateIs(
            $config,
            $expectedTimeout,
            $expectedSourceDirectories,
            $expectedSourceFiles,
            $expectedLogs,
            $expectedLogVerbosity,
            normalizePath($expectedTmpDir),
            $expectedPhpUnit,
            $expectedMutators,
            $expectedTestFramework,
            $expectedBootstrap,
            $expectedInitialTestsPhpOptions,
            $expectedTestFrameworkOptions,
            normalizePath($expectedExistingCoverageBasePath),
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
            '',
            null,
            null,
            '',
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            new PhpUnitExtraOptions(),
            sys_get_temp_dir() . '/infection',
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

        yield 'no existing base path for code coverage' => self::createValueForCoverageBasePath(
            null,
            sys_get_temp_dir() . '/infection'
        );

        yield 'absolute base path for code coverage' => self::createValueForCoverageBasePath(
            '/path/to/coverage',
            '/path/to/coverage'
        );

        yield 'relative base path for code coverage' => self::createValueForCoverageBasePath(
            'relative/path/to/coverage',
            '/path/to/relative/path/to/coverage'
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
            'phpunit',
            new PhpUnitExtraOptions()
        );

        yield 'test framework from config' => self::createValueForTestFramework(
            'phpspec',
            null,
            'phpspec',
            new PhpSpecExtraOptions()
        );

        yield 'test framework from input' => self::createValueForTestFramework(
            null,
            'phpspec',
            'phpspec',
            new PhpSpecExtraOptions()
        );

        yield 'test framework from config & input' => self::createValueForTestFramework(
            'phpunit',
            'phpspec',
            'phpspec',
            new PhpSpecExtraOptions()
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

        yield 'test no framework PHP options' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            null,
            null,
            new PhpUnitExtraOptions()
        );

        yield 'test framework PHP options from config' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            '--debug',
            null,
            new PhpUnitExtraOptions('--debug')
        );

        yield 'test framework PHP options from input' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            null,
            '--debug',
            new PhpUnitExtraOptions('--debug')
        );

        yield 'test framework PHP options from config & input' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            '--stop-on-failure',
            '--debug',
            new PhpUnitExtraOptions('--debug')
        );

        yield 'test framework PHP options from config with phpspec framework' => self::createValueForTestFrameworkExtraOptions(
            'phpspec',
            '--debug',
            null,
            new PhpSpecExtraOptions('--debug')
        );

        yield 'PHPUnit test framework' => self::createValueForTestFrameworkKey(
            'phpunit',
            '--debug',
            new PhpUnitExtraOptions('--debug')
        );

        yield 'phpSpec test framework' => self::createValueForTestFrameworkKey(
            'phpspec',
            '--debug',
            new PhpSpecExtraOptions('--debug')
        );

        yield 'codeception test framework' => self::createValueForTestFrameworkKey(
            'codeception',
            '--debug',
            new PhpSpecExtraOptions('--debug')
        );

        yield 'no mutator' => self::createValueForMutators(
            [],
            '',
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
            '',
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

        yield 'with source files' => [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source(['src/'], ['vendor/']),
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
            '',
            null,
            null,
            'src/Foo.php, src/Bar.php',
            10,
            ['src/'],
            [
                new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
            ],
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
            'phpunit',
            null,
            null,
            new PhpUnitExtraOptions(),
            sys_get_temp_dir() . '/infection',
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            null,
        ];

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
            'src/Foo.php, src/Bar.php',
            10,
            ['src/'],
            [
                new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
            ],
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
            new PhpSpecExtraOptions('--stop-on-failure'),
            '/path/to/dist/coverage',
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
            '',
            null,
            null,
            '',
            $expectedTimeOut,
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            new PhpUnitExtraOptions(),
            sys_get_temp_dir() . '/infection',
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
            '',
            null,
            null,
            '',
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
            $expectedTmpDir,
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            new PhpUnitExtraOptions(),
            $expectedTmpDir,
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

    private static function createValueForCoverageBasePath(
        ?string $existingCoverageBasePath,
        string $expectedCoverageBasePath
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
                null
            ),
            $existingCoverageBasePath,
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
            '',
            null,
            null,
            '',
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            new PhpUnitExtraOptions(),
            $expectedCoverageBasePath,
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
            '',
            null,
            null,
            '',
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit($expectedPhpUnitConfigDir, null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            new PhpUnitExtraOptions(),
            sys_get_temp_dir() . '/infection',
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
        ?string $expectedTestFramework,
        // We are passing this expected result because we cannot do otherwise but it is not the
        // element under test in this scenario hence "resulting" instead of "expected"
        TestFrameworkExtraOptions $resultingTestFrameworkExtraOptions
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
            '',
            $inputTestFramework,
            null,
            '',
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            $expectedTestFramework,
            null,
            null,
            $resultingTestFrameworkExtraOptions,
            sys_get_temp_dir() . '/infection',
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
            '',
            null,
            null,
            '',
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            $expectedInitialTestPhpOptions,
            new PhpUnitExtraOptions(),
            sys_get_temp_dir() . '/infection',
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

    private static function createValueForTestFrameworkExtraOptions(
        string $configTestFramework,
        ?string $configTestsFrameworkExtraOptions,
        ?string $inputTestsFrameworkExtraOptions,
        TestFrameworkExtraOptions $expectedTestFrameworkOptions
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
                $configTestsFrameworkExtraOptions
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
            '',
            null,
            $inputTestsFrameworkExtraOptions,
            '',
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            $configTestFramework,
            null,
            null,
            $expectedTestFrameworkOptions,
            sys_get_temp_dir() . '/infection',
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

    private static function createValueForTestFrameworkKey(
        string $configTestFramework,
        string $inputTestsFrameworkOptions,
        TestFrameworkExtraOptions $expectedTestFrameworkExtraOptions
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
            '',
            null,
            $inputTestsFrameworkOptions,
            '',
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            $configTestFramework,
            null,
            null,
            $expectedTestFrameworkExtraOptions,
            sys_get_temp_dir() . '/infection',
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
        string $inputMutators,
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
            '',
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
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            $expectedMutators,
            'phpunit',
            null,
            null,
            new PhpUnitExtraOptions(),
            sys_get_temp_dir() . '/infection',
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
            self::$mutators = (new MutatorFactory())->create(['@default' => true]);
        }

        return self::$mutators;
    }
}
