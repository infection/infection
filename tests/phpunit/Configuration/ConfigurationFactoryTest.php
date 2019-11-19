<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use Generator;
use Infection\Configuration\Configuration;
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use PHPUnit\Framework\TestCase;
use function sys_get_temp_dir;

final class ConfigurationFactoryTest extends TestCase
{
    use ConfigurationAssertions;

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
        ?int $expectedTimeout,
        Source $expectedSource,
        Logs $expectedLogs,
        ?string $expectedLogVerbosity,
        ?string $expectedTmpDir,
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
        ?float $expectedMinCoveredMsi,
        ?string $expectedStringMutators
    ): void {
        $config = (new ConfigurationFactory())->create(
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
            $expectedTmpDir,
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
            $expectedMinCoveredMsi,
            $expectedStringMutators
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
                null,
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
            null,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir(),
            new PhpUnit('/path/to', null),
            [],
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
            null,
        ];

        yield 'null tmp dir' => self::createValueForTmpDir(
            null,
            sys_get_temp_dir()
        );

        yield 'empty tmp dir' => self::createValueForTmpDir(
            '',
            sys_get_temp_dir()
        );

        yield 'relative tmp dir path' => self::createValueForTmpDir(
            'relative/path/to/tmp',
            '/path/to/relative/path/to/tmp'
        );


        yield 'absolute tmp dir path' => self::createValueForTmpDir(
            '/absolute/path/to/tmp',
            '/absolute/path/to/tmp'
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
            '/path/to/config/tmp',
            new PhpUnit(
                '/path/to/config/phpunit-dir',
                'config/phpunit'
            ),
            ['@default' => true],
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
            'TrueValue',
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
            null,
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
            [],
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
                null,
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
            null,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir(),
            new PhpUnit($expectedPhpUnitConfigDir, null),
            [],
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
                null,
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
            null,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir(),
            new PhpUnit('/path/to', null),
            [],
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
                null,
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
            null,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir(),
            new PhpUnit('/path/to', null),
            [],
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
                null,
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
            null,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'none',
            sys_get_temp_dir(),
            new PhpUnit('/path/to', null),
            [],
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
            null,
        ];
    }
}
