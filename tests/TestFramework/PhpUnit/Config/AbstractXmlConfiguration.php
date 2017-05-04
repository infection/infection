<?php

declare(strict_types=1);


namespace Infection\Tests\TestFramework\PhpUnit\Config;


use Infection\TestFramework\PhpUnit\Config\InitialXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\MutationXmlConfiguration;
use PHPUnit\Framework\TestCase;

abstract class AbstractXmlConfiguration extends TestCase
{
    /**
     * @var InitialXmlConfiguration|MutationXmlConfiguration
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $pathToProject;

    /**
     * @var string
     */
    protected $tempDir = '/path/to/tmp';

    /**
     * @return InitialXmlConfiguration|MutationXmlConfiguration
     */
    abstract protected function getConfigurationObject();

    protected function setUp()
    {
        $this->pathToProject = realpath(__DIR__ . '/../../../Files/phpunit/project-path');

        $this->configuration = $this->getConfigurationObject();
    }

    public function test_it_replaces_test_suite_directory_wildcard()
    {
        $xml = $this->configuration->getXml();

        /** @var \DOMNodeList $directories */
        $directories = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/directory');

        $this->assertSame(2, $directories->length);
        $this->assertSame($this->pathToProject . '/AnotherBundle', $directories[0]->nodeValue);
        $this->assertSame($this->pathToProject . '/SomeBundle', $directories[1]->nodeValue);
    }

    public function test_it_removes_original_loggers()
    {
        $xml = $this->configuration->getXml();

        $nodeList = $this->queryXpath($xml, '/phpunit/logging/log[@type="coverage-html"]');

        $this->assertSame(0, $nodeList->length);
    }

    protected function queryXpath(string $xml, string $query)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xPath = new \DOMXPath($dom);

        return $xPath->query($query);
    }
}