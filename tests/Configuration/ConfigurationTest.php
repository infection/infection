<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use Generator;
use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use PHPUnit\Framework\TestCase;

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

                        }
                    }
                }
            }
        }
    }

    public function provideNullableStrictlyPositiveInteger(): Generator
    {
        yield null;
        yield 1;
        yield 10;
    }

    public function provideSource(): Generator
    {
        yield new Source([], []);
        yield new Source(['src', 'lib'], ['fixtures', 'tests']);
    }

    public function provideLogs(): Generator
    {
        foreach ([null, 'text.log'] as $text) {
            foreach ([null, 'summary.log'] as $summary) {
                foreach ([null, 'debug.log'] as $debug) {
                    foreach ([null, 'mutator.log'] as $mutator) {
                        foreach ($this->provideBadge as $badge) {
                            yield new Logs(
                                $text,
                                $summary,
                                $debug,
                                $mutator,
                                $badge
                            );
                        }
                    }
                }
            }
        }
    }

    public function provideNullableBadge(): Generator
    {
        yield null;
        yield new Badge('master');
    }

    public function providePhpUnit(): PhpUnit
    {
        foreach ([null, 'dist/phpunit'] as $configDir) {
            foreach ([null, 'bin/phpunit'] as $bin) {
                new PhpUnit($configDir, $bin);
            }
        }
    }

    public function provideNullableString(): Generator
    {
        yield null;
        yield '';
        yield '2836687504';
        yield '0ibjHvQAfl';
    }
}
