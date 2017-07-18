<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);


namespace Infection\Tests\TestFramework\PhpUnit\Config;


use Infection\TestFramework\PhpUnit\Config\InitialXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\MutationXmlConfiguration;
use function Infection\Tests\normalizePath as p;
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
        $this->pathToProject = p(realpath(__DIR__ . '/../../../Files/phpunit/project-path'));

        $this->configuration = $this->getConfigurationObject();
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