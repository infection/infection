<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Config;

use Infection\Finder\Exception\LocatorException;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use function Infection\Tests\normalizePath as p;

/**
 * @internal
 */
final class TestFrameworkConfigLocatorTest extends \PHPUnit\Framework\TestCase
{
    private $baseDir = __DIR__ . '/../../Fixtures/ConfigLocator/';

    public function test_it_throws_an_error_if_no_config_file_found(): void
    {
        $dir = $this->baseDir . 'NoFiles/';
        $locator = new TestFrameworkConfigLocator($dir);

        $this->expectException(LocatorException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The path %s does not contain any of the requested files: phpunit.xml, phpunit.yml, phpunit.xml.dist, phpunit.yml.dist, phpunit.dist.xml, phpunit.dist.yml',
                $dir
            )
        );

        $locator->locate('phpunit');
    }

    public function test_it_can_find_a_dist_file(): void
    {
        $dir = $this->baseDir . 'DistFile/';
        $locator = new TestFrameworkConfigLocator($dir);

        $output = $locator->locate('phpunit');

        $this->assertStringEndsWith(
            'tests/Fixtures/ConfigLocator/DistFile/phpunit.xml.dist',
            p($output),
            'Did not find the correct phpunit.xml.dist file.'
        );
    }

    public function test_it_can_find_an_alt_dist_file(): void
    {
        $dir = $this->baseDir . 'AltDistFile/';
        $locator = new TestFrameworkConfigLocator($dir);

        $output = $locator->locate('phpunit');

        $this->assertStringEndsWith(
            'tests/Fixtures/ConfigLocator/AltDistFile/phpunit.dist.xml',
            p($output),
            'Did not find the correct phpunit.xml.dist file.'
        );
    }

    public function test_it_can_find_an_xml_file(): void
    {
        $dir = $this->baseDir . 'XmlFile/';
        $locator = new TestFrameworkConfigLocator($dir);

        $output = $locator->locate('phpunit');

        $this->assertStringEndsWith(
            'tests/Fixtures/ConfigLocator/XmlFile/phpunit.xml',
            p($output),
            'Did not find the correct phpunit.xml file.'
        );
    }

    public function test_it_prefers_non_dist_files(): void
    {
        $dir = $this->baseDir . 'BothXmlAndDist/';
        $locator = new TestFrameworkConfigLocator($dir);

        $output = $locator->locate('phpunit');

        $this->assertStringEndsWith(
            'tests/Fixtures/ConfigLocator/BothXmlAndDist/phpunit.xml',
            p($output),
            'Did not find the correct phpunit.xml file.'
        );
    }

    public function test_config_dir_can_be_overwritten(): void
    {
        $dir = $this->baseDir . 'DistFile/';
        $locator = new TestFrameworkConfigLocator($dir);

        $output = $locator->locate('phpunit');

        $this->assertStringEndsWith(
            'tests/Fixtures/ConfigLocator/DistFile/phpunit.xml.dist',
            p($output),
            'Did not find the correct phpunit.xml.dist file.'
        );

        $this->expectException(LocatorException::class);
        $locator->locate('phpunit' . $dir . 'NoFiles/');
    }
}
