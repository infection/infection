<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\TestFramework\Config;

use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use function Infection\Tests\normalizePath as p;

class TestFrameworkConfigLocatorTest extends \PHPUnit\Framework\TestCase
{
    private $baseDir = __DIR__ . '/../../Fixtures/ConfigLocator/';

    public function test_it_throws_an_error_if_no_config_file_found()
    {
        $dir = $this->baseDir . 'NoFiles/';
        $locator = new TestFrameworkConfigLocator($dir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to locate phpunit(.dist).(xml|yml)(.dist) file.');

        $locator->locate('phpunit');
    }

    public function test_it_can_find_a_dist_file()
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

    public function test_it_can_find_an_alt_dist_file()
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

    public function test_it_can_find_an_xml_file()
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

    public function test_it_prefers_non_dist_files()
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

    public function test_config_dir_can_be_overwritten()
    {
        $dir = $this->baseDir . 'DistFile/';
        $locator = new TestFrameworkConfigLocator($dir);

        $output = $locator->locate('phpunit');

        $this->assertStringEndsWith(
            'tests/Fixtures/ConfigLocator/DistFile/phpunit.xml.dist',
            p($output),
            'Did not find the correct phpunit.xml.dist file.'
        );

        $this->expectException(\RuntimeException::class);
        $locator->locate('phpunit' . $dir . 'NoFiles/');
    }
}
