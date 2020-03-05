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
use Infection\Mutator\Boolean\TrueValueConfig;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorParser;
use Infection\Mutator\Removal\MethodCallRemoval;
use function Infection\Tests\normalizePath;
use Infection\Tests\SingletonContainer;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Finder\SplFileInfo;
use function sys_get_temp_dir;

/**
 * @group integration
 */
final class ConfigurationFactoryTest extends TestCase
{
    use ConfigurationAssertions;

    /**
     * @var array<string, Mutator>|null
     */
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
            SingletonContainer::getContainer()->getMutatorResolver(),
            SingletonContainer::getContainer()->getMutatorFactory(),
            new MutatorParser(),
            $sourceFilesCollectorProphecy->reveal()
        );
    }

    /**
     * @dataProvider valueProvider
     *
     * @param SplFileInfo[] $expectedSourceDirectories
     * @param SplFileInfo[] $expectedSourceFiles
     * @param Mutator[] $expectedMutators
     */
    public function test_it_can_create_a_configuration(
        SchemaConfiguration $schema,
        ?string $inputExistingCoveragePath,
        ?string $inputInitialTestsPhpOptions,
        bool $skipInitialTests,
        string $inputLogVerbosity,
        bool $inputDebug,
        bool $inputOnlyCovered,
        string $inputFormatter,
        bool $inputNoProgress,
        ?bool $inputIgnoreMsiWithNoMutations,
        ?float $inputMinMsi,
        bool $inputShowMutations,
        ?float $inputMinCoveredMsi,
        string $inputMutators,
        ?string $inputTestFramework,
        ?string $inputTestFrameworkExtraOptions,
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
        bool $expectedSkipInitialTests,
        string $expectedTestFrameworkExtraOptions,
        string $expectedCoveragePath,
        bool $expectedSkipCoverage,
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
            $skipInitialTests,
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
            $inputTestFrameworkExtraOptions,
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
            $expectedTestFrameworkExtraOptions,
            normalizePath($expectedCoveragePath),
            $expectedSkipCoverage,
            $expectedSkipInitialTests,
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
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
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

        yield 'no existing base path for code coverage' => self::createValueForCoveragePath(
            null,
            false,
            sys_get_temp_dir() . '/infection/coverage-xml'
        );

        yield 'absolute base path for code coverage' => self::createValueForCoveragePath(
            '/path/to/coverage',
            true,
            '/path/to/coverage/coverage-xml'
        );

        yield 'relative base path for code coverage' => self::createValueForCoveragePath(
            'relative/path/to/coverage',
            true,
            '/path/to/relative/path/to/coverage/coverage-xml'
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

        yield 'ignoreMsiWithNoMutations not specified in schema and not specified in input' => self::createValueForIgnoreMsiWithNoMutations(
            null,
            null,
            false
        );

        yield 'ignoreMsiWithNoMutations not specified in schema and true in input' => self::createValueForIgnoreMsiWithNoMutations(
            null,
            true,
            true
        );

        yield 'ignoreMsiWithNoMutations not specified in schema and false in input' => self::createValueForIgnoreMsiWithNoMutations(
            null,
            false,
            false
        );

        yield 'ignoreMsiWithNoMutations true in schema and not specified in input' => self::createValueForIgnoreMsiWithNoMutations(
            true,
            null,
            true
        );

        yield 'ignoreMsiWithNoMutations false in schema and not specified in input' => self::createValueForIgnoreMsiWithNoMutations(
            false,
            null,
            false
        );

        yield 'ignoreMsiWithNoMutations true in schema and false in input' => self::createValueForIgnoreMsiWithNoMutations(
            true,
            false,
            false
        );

        yield 'ignoreMsiWithNoMutations false in schema and true in input' => self::createValueForIgnoreMsiWithNoMutations(
            false,
            true,
            true
        );

        yield 'minMsi not specified in schema and not specified in input' => self::createValueForMinMsi(
            null,
            null,
            null
        );

        yield 'minMsi specified in schema and not specified in input' => self::createValueForMinMsi(
            33.3,
            null,
            33.3
        );

        yield 'minMsi not specified in schema and specified in input' => self::createValueForMinMsi(
            null,
            21.2,
            21.2
        );

        yield 'minMsi specified in schema and specified in input' => self::createValueForMinMsi(
            33.3,
            21.2,
            21.2
        );

        yield 'minCoveredMsi not specified in schema and not specified in input' => self::createValueForminCoveredMsi(
            null,
            null,
            null
        );

        yield 'minCoveredMsi specified in schema and not specified in input' => self::createValueForminCoveredMsi(
            33.3,
            null,
            33.3
        );

        yield 'minCoveredMsi not specified in schema and specified in input' => self::createValueForminCoveredMsi(
            null,
            21.2,
            21.2
        );

        yield 'minCoveredMsi specified in schema and specified in input' => self::createValueForminCoveredMsi(
            33.3,
            21.2,
            21.2
        );

        yield 'no test framework' => self::createValueForTestFramework(
            null,
            null,
            'phpunit',
            '',
            sys_get_temp_dir() . '/infection/coverage-xml'
        );

        yield 'test framework from config' => self::createValueForTestFramework(
            'phpspec',
            null,
            'phpspec',
            '',
            sys_get_temp_dir() . '/infection/phpspec-coverage-xml'
        );

        yield 'test framework from input' => self::createValueForTestFramework(
            null,
            'phpspec',
            'phpspec',
            '',
            sys_get_temp_dir() . '/infection/phpspec-coverage-xml'
        );

        yield 'test framework from config & input' => self::createValueForTestFramework(
            'phpunit',
            'phpspec',
            'phpspec',
            '',
            sys_get_temp_dir() . '/infection/phpspec-coverage-xml'
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
            '',
            sys_get_temp_dir() . '/infection/coverage-xml'
        );

        yield 'test framework PHP options from config' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            '--debug',
            null,
            '--debug',
            sys_get_temp_dir() . '/infection/coverage-xml'
        );

        yield 'test framework PHP options from input' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            null,
            '--debug',
            '--debug',
            sys_get_temp_dir() . '/infection/coverage-xml'
        );

        yield 'test framework PHP options from config & input' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            '--stop-on-failure',
            '--debug',
            '--debug',
            sys_get_temp_dir() . '/infection/coverage-xml'
        );

        yield 'test framework PHP options from config with phpspec framework' => self::createValueForTestFrameworkExtraOptions(
            'phpspec',
            '--debug',
            null,
            '--debug',
            sys_get_temp_dir() . '/infection/phpspec-coverage-xml'
        );

        yield 'PHPUnit test framework' => self::createValueForTestFrameworkKey(
            'phpunit',
            '--debug',
            '--debug',
            sys_get_temp_dir() . '/infection/coverage-xml'
        );

        yield 'phpSpec test framework' => self::createValueForTestFrameworkKey(
            'phpspec',
            '--debug',
            '--debug',
            sys_get_temp_dir() . '/infection/phpspec-coverage-xml'
        );

        yield 'codeception test framework' => self::createValueForTestFrameworkKey(
            'codeception',
            '--debug',
            '--debug',
            sys_get_temp_dir() . '/infection/codeception-coverage-xml'
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
                        'Infection\FileSystem\Finder\SourceFilesFinder::__construct::63',
                    ],
                ],
            ],
            '',
            [
                'MethodCallRemoval' => new IgnoreMutator(
                    new IgnoreConfig([
                        'Infection\FileSystem\Finder\SourceFilesFinder::__construct::63',
                    ]),
                    new MethodCallRemoval()
                ),
            ]
        );

        yield 'mutators from config & input' => self::createValueForMutators(
            [
                '@default' => true,
                'MethodCallRemoval' => (object) [
                    'ignore' => [
                        'Infection\FileSystem\Finder\SourceFilesFinder::__construct::63',
                    ],
                ],
            ],
            'AssignmentEqual,EqualIdentical',
            (static function (): array {
                return [
                    'AssignmentEqual' => new IgnoreMutator(
                        new IgnoreConfig([]),
                        new AssignmentEqual()
                    ),
                    'EqualIdentical' => new IgnoreMutator(
                        new IgnoreConfig([]),
                        new EqualIdentical()
                    ),
                ];
            })()
        );

        yield 'with source files' => [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source(['src/'], ['vendor/']),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
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
                null,
                null,
                null,
                ['@default' => true],
                'phpunit',
                'config/bootstrap.php',
                '-d zend_extension=wrong_xdebug.so',
                '--debug'
            ),
            'dist/coverage',
            '-d zend_extension=xdebug.so',
            false,
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
            (static function (): array {
                return [
                    'TrueValue' => new IgnoreMutator(
                        new IgnoreConfig([]),
                        new TrueValue(new TrueValueConfig([]))
                    ),
                ];
            })(),
            'phpspec',
            'config/bootstrap.php',
            '-d zend_extension=xdebug.so',
            false,
            '--stop-on-failure',
            '/path/to/dist/coverage/phpspec-coverage-xml',
            true,
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
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
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
                Logs::createEmpty(),
                $configTmpDir,
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
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
            Logs::createEmpty(),
            'none',
            $expectedTmpDir,
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            $expectedTmpDir . '/coverage-xml',
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
    }

    private static function createValueForCoveragePath(
        ?string $existingCoveragePath,
        bool $expectedSkipCoverage,
        string $expectedCoveragePath
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null
            ),
            $existingCoveragePath,
            null,
            false,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            $expectedCoveragePath,
            $expectedSkipCoverage,
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
                Logs::createEmpty(),
                '',
                new PhpUnit($phpUnitConfigDir, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit($expectedPhpUnitConfigDir, null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
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
    }

    private static function createValueForIgnoreMsiWithNoMutations(
        ?bool $ignoreMsiWithNoMutationsFromSchemaConfiguration,
        ?bool $ignoreMsiWithNoMutationsFromInput,
        ?bool $expectedIgnoreMsiWithNoMutations
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit('/path/to', null),
                $ignoreMsiWithNoMutationsFromSchemaConfiguration,
                null,
                null,
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
            'none',
            false,
            false,
            'progress',
            false,
            $ignoreMsiWithNoMutationsFromInput,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
            false,
            false,
            false,
            'progress',
            false,
            $expectedIgnoreMsiWithNoMutations,
            null,
            false,
            null,
        ];
    }

    private static function createValueForMinMsi(
        ?float $minMsiFromSchemaConfiguration,
        ?float $minMsiFromInput,
        ?float $expectedMinMsi
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit('/path/to', null),
                null,
                $minMsiFromSchemaConfiguration,
                null,
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
            'none',
            false,
            false,
            'progress',
            false,
            null,
            $minMsiFromInput,
            false,
            null,
            '',
            null,
            null,
            '',
            10,
            [],
            [],
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
            false,
            false,
            false,
            'progress',
            false,
            false,
            $expectedMinMsi,
            false,
            null,
        ];
    }

    private static function createValueForMinCoveredMsi(
        ?float $minCoveredMsiFromSchemaConfiguration,
        ?float $minCoveredMsiFromInput,
        ?float $expectedMinCoveredMsi
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit('/path/to', null),
                null,
                null,
                $minCoveredMsiFromSchemaConfiguration,
                [],
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
            'none',
            false,
            false,
            'progress',
            false,
            null,
            null,
            false,
            $minCoveredMsiFromInput,
            '',
            null,
            null,
            '',
            10,
            [],
            [],
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            null,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
            false,
            false,
            false,
            'progress',
            false,
            false,
            null,
            false,
            $expectedMinCoveredMsi,
        ];
    }

    private static function createValueForTestFramework(
        ?string $configTestFramework,
        ?string $inputTestFramework,
        string $expectedTestFramework,
        string $expectedTestFrameworkExtraOptions,
        string $expectedCoveragePath
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                $configTestFramework,
                null,
                null,
                null
            ),
            null,
            null,
            false,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            $expectedTestFramework,
            null,
            null,
            false,
            $expectedTestFrameworkExtraOptions,
            $expectedCoveragePath,
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
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                $configInitialTestsPhpOptions,
                null
            ),
            null,
            $inputInitialTestsPhpOptions,
            false,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            'phpunit',
            null,
            $expectedInitialTestPhpOptions,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
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
    }

    private static function createValueForTestFrameworkExtraOptions(
        string $configTestFramework,
        ?string $configTestFrameworkExtraOptions,
        ?string $inputTestFrameworkExtraOptions,
        string $expectedTestFrameworkExtraOptions,
        string $expectedCoveragePath
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                $configTestFramework,
                null,
                null,
                $configTestFrameworkExtraOptions
            ),
            null,
            null,
            false,
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
            $inputTestFrameworkExtraOptions,
            '',
            10,
            [],
            [],
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            $configTestFramework,
            null,
            null,
            false,
            $expectedTestFrameworkExtraOptions,
            $expectedCoveragePath,
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
    }

    private static function createValueForTestFrameworkKey(
        string $configTestFramework,
        string $inputTestFrameworkExtraOptions,
        string $expectedTestFrameworkExtraOptions,
        string $expectedCoveragePath
    ): array {
        return [
            new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                null,
                null,
                null,
                [],
                $configTestFramework,
                null,
                null,
                null
            ),
            null,
            null,
            false,
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
            $inputTestFrameworkExtraOptions,
            '',
            10,
            [],
            [],
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            self::getDefaultMutators(),
            $configTestFramework,
            null,
            null,
            false,
            $expectedTestFrameworkExtraOptions,
            $expectedCoveragePath,
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
    }

    /**
     * @param array<string, Mutator> $expectedMutators
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
                Logs::createEmpty(),
                null,
                new PhpUnit(null, null),
                null,
                null,
                null,
                $configMutators,
                null,
                null,
                null,
                null
            ),
            null,
            null,
            false,
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
            Logs::createEmpty(),
            'none',
            sys_get_temp_dir() . '/infection',
            new PhpUnit('/path/to', null),
            $expectedMutators,
            'phpunit',
            null,
            null,
            false,
            '',
            sys_get_temp_dir() . '/infection/coverage-xml',
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
    }

    /**
     * @return array<string, Mutator>
     */
    private static function getDefaultMutators(): array
    {
        if (self::$mutators === null) {
            self::$mutators = SingletonContainer::getContainer()
                ->getMutatorFactory()
                ->create(
                    SingletonContainer::getContainer()
                        ->getMutatorResolver()
                        ->resolve(['@default' => true])
                )
            ;
        }

        return self::$mutators;
    }
}
