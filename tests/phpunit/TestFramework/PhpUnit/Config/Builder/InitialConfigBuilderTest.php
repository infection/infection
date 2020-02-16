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
use Infection\TestFramework\PhpUnit\Config\Exception\InvalidPhpUnitXmlConfigException;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\Tests\FileSystem\FileSystemTestCase;
use InvalidArgumentException;
use function Infection\Tests\normalizePath as p;
use function Safe\file_get_contents;
use function Safe\realpath;
use function Safe\sprintf;
use Symfony\Component\Filesystem\Filesystem;
use function simplexml_load_string;

/**
 * @group integration Requires some I/O operations
 */
final class InitialConfigBuilderTest extends FileSystemTestCase
{
    private const FIXTURES = __DIR__ . '/../../../../Fixtures/Files/phpunit';

    public const HASH = 'a1b2c3';

    /**
     * @var string
     */
    private $projectPath;

    /**
     * @var InitialConfigBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectPath = p(realpath(self::FIXTURES.'/project-path'));

        $this->builder = $this->createConfigBuilder();
    }

    public function test_it_builds_and_dump_the_XML_configuration(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $this->assertSame(
            $this->tmp.'/phpunitConfiguration.initial.infection.xml',
            $configurationPath
        );

        $this->assertFileExists($configurationPath);

        $xml = file_get_contents($configurationPath);

        $this->assertNotFalse(
            @simplexml_load_string($xml),
            'Expected dumped configuration content to be a valid XML file.'
        );
    }

    public function test_it_preserves_white_spaces_and_formatting(): void
    {
        $builder = $this->createConfigBuilder(
            self::FIXTURES.'/format-whitespace/original-phpunit.xml',
            true
        );

        $configurationPath = $builder->build('6.5');

        $this->assertFileEquals(
            self::FIXTURES.'/format-whitespace/expected-phpunit.xml',
            $configurationPath
        );
    }

    public function test_the_original_XML_config_must_be_a_valid_XML_file(): void
    {
        try {
            $this->createConfigBuilder(
                self::FIXTURES.'/invalid/empty-phpunit.xml',
                true
            );

            $this->fail('Expected an exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'The original XML config content cannot be an empty string',
                $exception->getMessage()
            );
        }
    }

    public function test_the_original_XML_config_must_be_a_valid_PHPUnit_config_file(): void
    {
        $builder = $this->createConfigBuilder(
            self::FIXTURES.'/invalid/invalid-phpunit.xml',
            true
        );

        try {
            $builder->build('x');

            $this->fail('Expected an exception to be thrown.');
        } catch (InvalidPhpUnitXmlConfigException $exception) {
            $this->assertSame(
                'phpunit.xml does not contain a valid PHPUnit configuration.',
                $exception->getMessage()
            );
        }
    }

    public function test_it_replaces_relative_path_to_absolute_path(): void
    {
        $xml = file_get_contents($this->builder->build('6.5'));

        $directories = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/directory');

        $this->assertInstanceOf(DOMNodeList::class, $directories);

        $this->assertSame(1, $directories->length);
        $this->assertSame($this->projectPath . '/*Bundle', p($directories[0]->nodeValue));
    }

    public function test_it_replaces_bootstrap_file(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $xml = file_get_contents($configurationPath);

        $value = p($this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue);

        $this->assertSame($this->projectPath . '/app/autoload2.php', $value);
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
        $builder = $this->createConfigBuilder(null, true);

        $xml = file_get_contents($builder->build('6.5'));

        /** @var DOMNodeList $logEntries */
        $logEntries = $this->queryXpath($xml, '/phpunit/logging/log');

        $this->assertSame(0, $logEntries->length);
    }

    public function test_it_creates_coverage_filter_whitelist_node_if_does_not_exist(): void
    {
        $phpunitXmlPath = self::FIXTURES.'/phpunit_without_coverage_whitelist.xml';

        $xml = file_get_contents($this->createConfigBuilder($phpunitXmlPath)->build('6.5'));

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
        $phpunitXmlPath = self::FIXTURES.'/phpunit_with_order_set.xml';

        $builder = $this->createConfigBuilder($phpunitXmlPath);

        $xml = file_get_contents($builder->build('7.2'));

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

        return (new DOMXPath($dom))->query($query);
    }

    private function createConfigBuilder(
        ?string $originalPhpUnitXmlConfigPath = null,
        bool $skipCoverage = false
    ): InitialConfigBuilder
    {
        $phpunitXmlPath = $originalPhpUnitXmlConfigPath ?: self::FIXTURES.'/phpunit.xml';

        $jUnitFilePath = '/path/to/junit.xml';
        $srcDirs = ['src', 'app'];

        $replacer = new PathReplacer(new Filesystem(), $this->projectPath);

        return new InitialConfigBuilder(
            $this->tmp,
            file_get_contents($phpunitXmlPath),
            new XmlConfigurationHelper($replacer, ''),
            $jUnitFilePath,
            $srcDirs,
            $skipCoverage
        );
    }
}
