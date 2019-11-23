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

namespace Infection\Tests\TestFramework\Config;

use Infection\Locator\FileOrDirectoryNotFound;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use function Infection\Tests\normalizePath as p;

final class TestFrameworkConfigLocatorTest extends \PHPUnit\Framework\TestCase
{
    private $baseDir = __DIR__ . '/../../Fixtures/ConfigLocator/';

    public function test_it_throws_an_error_if_no_config_file_found(): void
    {
        $dir = $this->baseDir . 'NoFiles/';
        $locator = new TestFrameworkConfigLocator($dir);

        $this->expectException(FileOrDirectoryNotFound::class);
        $this->expectExceptionMessage(
            sprintf(
                'The path "%s" does not contain any of the requested files: "phpunit.xml", "phpunit.yml", "phpunit.xml.dist", "phpunit.yml.dist", "phpunit.dist.xml", "phpunit.dist.yml"',
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
            'tests/phpunit/Fixtures/ConfigLocator/DistFile/phpunit.xml.dist',
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
            'tests/phpunit/Fixtures/ConfigLocator/AltDistFile/phpunit.dist.xml',
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
            'tests/phpunit/Fixtures/ConfigLocator/XmlFile/phpunit.xml',
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
            'tests/phpunit/Fixtures/ConfigLocator/BothXmlAndDist/phpunit.xml',
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
            'tests/phpunit/Fixtures/ConfigLocator/DistFile/phpunit.xml.dist',
            p($output),
            'Did not find the correct phpunit.xml.dist file.'
        );

        $this->expectException(FileOrDirectoryNotFound::class);
        $locator->locate('phpunit' . $dir . 'NoFiles/');
    }
}
