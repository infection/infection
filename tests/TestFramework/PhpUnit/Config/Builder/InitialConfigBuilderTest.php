<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config\Builder;

use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\Utils\TempDirectoryCreator;
use Mockery;
use function Infection\Tests\normalizePath as p;

class InitialConfigBuilderTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    const HASH = 'a1b2c3';

    private $tempDir;

    private $pathToProject;

    /**
     * @var InitialConfigBuilder
     */
    private $builder;

    protected function setUp()
    {
        $tempDirCreator = new TempDirectoryCreator();
        $this->tempDir = $tempDirCreator->createAndGet(
            'infection-test' . \microtime(true) . \random_int(100, 999)
        );

        $this->pathToProject = p(realpath(__DIR__ . '/../../../../Fixtures/Files/phpunit/project-path'));

        $this->createConfigBuilder();
    }

    private function createConfigBuilder($phpUnitXmlConfigPath = null)
    {
        $phpunitXmlPath = $phpUnitXmlConfigPath ?: __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit.xml';

        $jUnitFilePath = '/path/to/junit.xml';
        $srcDirs = ['src', 'app'];

        $replacer = new PathReplacer(new Locator([$this->pathToProject]));

        $this->builder = new InitialConfigBuilder(
            $this->tempDir,
            file_get_contents($phpunitXmlPath),
            new XmlConfigurationHelper($replacer),
            $jUnitFilePath,
            $srcDirs
        );
    }

    protected function tearDown()
    {
        @\unlink($this->tempDir);
    }

    public function test_it_replaces_test_suite_directory_wildcard()
    {
        $configurationPath = $this->builder->build();

        $xml = file_get_contents($configurationPath);

        /** @var \DOMNodeList $directories */
        $directories = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/directory');

        $this->assertSame(2, $directories->length);
        $this->assertSame($this->pathToProject . '/AnotherBundle', p($directories[0]->nodeValue));
        $this->assertSame($this->pathToProject . '/SomeBundle', p($directories[1]->nodeValue));
    }

    public function test_it_replaces_bootstrap_file()
    {
        $configurationPath = $this->builder->build();

        $xml = file_get_contents($configurationPath);

        $value = p($this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue);

        $this->assertSame($this->pathToProject . '/app/autoload2.php', $value);
    }

    public function test_it_removes_original_loggers()
    {
        $configurationPath = $this->builder->build();

        $xml = file_get_contents($configurationPath);

        $nodeList = $this->queryXpath($xml, '/phpunit/logging/log[@type="coverage-html"]');

        $this->assertSame(0, $nodeList->length);
    }

    public function test_it_adds_needed_loggers()
    {
        $configurationPath = $this->builder->build();

        $xml = file_get_contents($configurationPath);

        /** @var \DOMNodeList $logEntries */
        $logEntries = $this->queryXpath($xml, '/phpunit/logging/log');

        $this->assertSame(2, $logEntries->length);
        $this->assertSame($this->tempDir . '/coverage-xml', $logEntries[0]->getAttribute('target'));
        $this->assertSame('coverage-xml', $logEntries[0]->getAttribute('type'));
        $this->assertSame('junit', $logEntries[1]->getAttribute('type'));
    }

    public function test_it_creates_coverage_filter_whitelist_node_if_does_not_exist()
    {
        $phpunitXmlPath = __DIR__ . '/../../../../Fixtures/Files/phpunit/phpunit_without_coverage_whitelist.xml';
        $configuration = $this->createConfigBuilder($phpunitXmlPath);

        $configurationPath = $this->builder->build();

        $xml = file_get_contents($configurationPath);

        /** @var \DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, '/phpunit/filter/whitelist/directory');

        $this->assertSame(2, $filterNodes->length);
    }

    public function test_it_does_not_create_coverage_filter_whitelist_node_if_already_exist()
    {
        $configurationPath = $this->builder->build();

        $xml = file_get_contents($configurationPath);

        /** @var \DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, '/phpunit/filter/whitelist/directory');

        $this->assertSame(1, $filterNodes->length);
    }

    public function test_it_removes_printer_class()
    {
        $configurationPath = $this->builder->build();

        $xml = file_get_contents($configurationPath);

        /** @var \DOMNodeList $filterNodes */
        $filterNodes = $this->queryXpath($xml, '/phpunit/@printerClass');
        $this->assertSame(0, $filterNodes->length);
    }

    protected function queryXpath(string $xml, string $query)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xPath = new \DOMXPath($dom);

        return $xPath->query($query);
    }
}
