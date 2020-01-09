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

namespace Infection\Tests\TestFramework\PhpUnit\Config\Builder;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Generator;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\Tests\FileSystem\FileSystemTestCase;
use function Infection\Tests\normalizePath as p;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group integration Requires I/O reads
 */
final class InitialConfigBuilderTest extends FileSystemTestCase
{
    public const HASH = 'a1b2c3';

    /**
     * @var string
     */
    private $pathToProject;

    /**
     * @var InitialConfigBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pathToProject = p(realpath(__DIR__ . '/../../../../Fixtures/Files/phpunit/project-path'));

        $this->createConfigBuilder();
    }

    public function test_it_replaces_relative_path_to_absolute_path(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        /** @var DOMNodeList $directories */
        $directories = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/directory');

        $this->assertSame(1, $directories->length);
        $this->assertSame($this->pathToProject . '/*Bundle', p($directories[0]->nodeValue));
    }

    public function test_it_replaces_bootstrap_file(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        $value = p($this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue);

        $this->assertSame($this->pathToProject . '/app/autoload2.php', $value);
    }

    public function test_it_removes_original_loggers(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        $nodeList = $this->queryXpath($xml, '/phpunit/logging/log[@type="coverage-html"]');

        $this->assertSame(0, $nodeList->length);
    }

    public function test_it_adds_needed_loggers(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        /** @var DOMNodeList $logEntries */
        $logEntries = $this->queryXpath($xml, '/phpunit/logging/log');

        $this->assertSame(2, $logEntries->length);
        $this->assertSame($this->tmp . '/coverage-xml', $logEntries[0]->getAttribute('target'));
        $this->assertSame('coverage-xml', $logEntries[0]->getAttribute('type'));
        $this->assertSame('junit', $logEntries[1]->getAttribute('type'));
    }

    public function test_it_does_not_add_coverage_loggers_if_should_be_skipped(): void
    {
        $this->createConfigBuilder(null, true);
        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        /** @var DOMNodeList $logEntries */
        $logEntries = $this->queryXpath($xml, '/phpunit/logging/log');

        $this->assertSame(0, $logEntries->length);
    }

    public function test_it_creates_coverage_filter_whitelist_node_if_does_not_exist(): void
    {
        $phpunitXmlPath = __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit_without_coverage_whitelist.xml';
        $this->createConfigBuilder($phpunitXmlPath);

        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        /** @var DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, '/phpunit/filter/whitelist/directory');

        $this->assertSame(2, $filterNodes->length);
    }

    public function test_it_does_not_create_coverage_filter_whitelist_node_if_already_exist(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        /** @var DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, '/phpunit/filter/whitelist/directory');

        $this->assertSame(1, $filterNodes->length);
    }

    public function test_it_removes_printer_class(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        /** @var DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, '/phpunit/@printerClass');
        $this->assertSame(0, $filterNodes->length);
    }

    /**
     * @dataProvider executionOrderProvider
     */
    public function test_it_adds_execution_order_for_proper_phpunit_versions(string $version, string $attributeName, int $expectedNodeCount): void
    {
        $configurationPath = $this->builder->build($version);

        $xml = file_get_contents($configurationPath);

        /** @var DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, sprintf('/phpunit/@%s', $attributeName));
        $this->assertSame($expectedNodeCount, $filterNodes->length);
    }

    public function test_it_does_not_update_order_if_it_is_already_set(): void
    {
        $phpunitXmlPath = __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit_with_order_set.xml';
        $this->createConfigBuilder($phpunitXmlPath);

        $configurationPath = $this->builder->build('7.2');

        $xml = file_get_contents($configurationPath);

        /** @var DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, sprintf('/phpunit/@%s', 'executionOrder'));
        $this->assertSame('reverse', $filterNodes[0]->value);

        $filterNodes = $this->queryXpath($xml, sprintf('/phpunit/@%s', 'resolveDependencies'));
        $this->assertSame(0, $filterNodes->length);
    }

    public function executionOrderProvider(): Generator
    {
        yield 'PHPUnit 7.1.99 runs without random test order' => ['7.1.99', 'executionOrder', 0];

        yield 'PHPUnit 7.2 runs with random test order' => ['7.2', 'executionOrder', 1];

        yield 'PHPUnit 7.3.1 runs with random test order' => ['7.3.1', 'executionOrder', 1];

        yield 'PHPUnit 7.1.99 runs without dependency resolver' => ['7.1.99', 'resolveDependencies', 0];

        yield 'PHPUnit 7.2 runs with dependency resolver' => ['7.2', 'resolveDependencies', 1];

        yield 'PHPUnit 7.3.1 runs dependency resolver' => ['7.3.1', 'resolveDependencies', 1];
    }

    private function queryXpath(string $xml, string $query)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xPath = new DOMXPath($dom);

        return $xPath->query($query);
    }

    private function createConfigBuilder(?string $phpUnitXmlConfigPath = null, bool $skipCoverage = false): void
    {
        $phpunitXmlPath = $phpUnitXmlConfigPath ?: __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit.xml';

        $jUnitFilePath = '/path/to/junit.xml';
        $srcDirs = ['src', 'app'];

        $replacer = new PathReplacer(new Filesystem(), $this->pathToProject);

        $this->builder = new InitialConfigBuilder(
            $this->tmp,
            file_get_contents($phpunitXmlPath),
            new XmlConfigurationHelper($replacer, ''),
            $jUnitFilePath,
            $srcDirs,
            $skipCoverage
        );
    }
}
