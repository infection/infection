<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Entry;

use Generator;
use Infection\Configuration\Entry\PhpUnit;
use PHPUnit\Framework\TestCase;

class PhpUnitTest extends TestCase
{
    use PhpUnitAssertions;

    /**
     * @dataProvider valuesProvider
     */
    public function test_it_can_be_instantiated(
        ?string $configDir,
        ?string $executablePath
    ): void
    {
        $phpUnit = new PhpUnit($configDir, $executablePath);

        $this->assertPhpUnitStateIs($phpUnit, $configDir, $executablePath);
    }

    public function test_it_can_change_its_configuration_dir(): void
    {
        $phpUnit = new PhpUnit(
            '/path/to/phpunit-config',
            '/path/to/phpunit'
        );

        $phpUnit->setConfigDir('/path/to/another-phpunit-config');

        $this->assertPhpUnitStateIs(
            $phpUnit,
            '/path/to/another-phpunit-config',
            '/path/to/phpunit'
        );
    }

    public function valuesProvider(): Generator
    {
        yield 'minimal' => [
            null,
            null,
        ];

        yield 'complete' => [
            '/path/to/phpunit-config',
            '/path/to/phpunit',
        ];
    }
}
