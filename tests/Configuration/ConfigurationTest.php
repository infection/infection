<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use Generator;
use Infection\Configuration\Configuration;
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
use function var_export;

class ConfigurationTest extends TestCase
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
    ): void
    {
        $config = new Configuration(
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
                                                    $testFrameworkOptions
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
