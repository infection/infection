<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config;

use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\InitialXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use PHPUnit\Framework\TestCase;

class InitialXmlConfigurationTest extends TestCase
{
    /**
     * @var InitialXmlConfiguration
     */
    private $configuration;

    /**
     * @var string
     */
    private $pathToProject;

    /**
     * @var string
     */
    private $tempDir = '/path/to/tmp';

    protected function setUp()
    {
        $phpunitXmlPath = __DIR__ . '/../../../Files/phpunit/phpunit.xml';
        $this->pathToProject = realpath(__DIR__ . '/../../../Files/phpunit/project-path');

        $replacer = new PathReplacer(new Locator($this->pathToProject));

        $this->configuration = new InitialXmlConfiguration($this->tempDir, $phpunitXmlPath, $replacer);
    }

    public function test_it_replaces_bootstrap_file()
    {
        $xml = $this->configuration->getXml();

        $value = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $this->assertSame($this->pathToProject . '/app/autoload2.php', $value);
    }

    public function test_it_replaces_test_suite_director_wildcard()
    {
        $xml = $this->configuration->getXml();

        /** @var \DOMNodeList $directories */
        $directories = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/directory');

        $this->assertSame(2, $directories->length);
        $this->assertSame($this->pathToProject . '/AnotherBundle', $directories[0]->nodeValue);
        $this->assertSame($this->pathToProject . '/SomeBundle', $directories[1]->nodeValue);
    }

    public function test_it_adds_php_logger()
    {
        $xml = $this->configuration->getXml();

        /** @var \DOMNodeList $logEntries */
        $logEntries = $this->queryXpath($xml, '/phpunit/logging/log');

        /** @var \DOMNamedNodeMap $attributes */
        $attributes = $logEntries[0]->attributes;

        $this->assertSame(1, $logEntries->length);
        $this->assertSame($this->tempDir . '/coverage.infection.php', $attributes->getNamedItem('target')->value);
        $this->assertSame('coverage-php', $attributes->getNamedItem('type')->value);
    }

    private function queryXpath(string $xml, string $query)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xPath = new \DOMXPath($dom);

        return $xPath->query($query);
    }
}
