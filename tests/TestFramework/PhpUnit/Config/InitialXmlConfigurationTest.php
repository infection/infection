<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config;

use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\InitialXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class InitialXmlConfigurationTest extends AbstractXmlConfiguration
{
    protected function getConfigurationObject()
    {
        $phpunitXmlPath = __DIR__ . '/../../../Files/phpunit/phpunit.xml';

        $replacer = new PathReplacer(new Locator($this->pathToProject));

        return new InitialXmlConfiguration($this->tempDir, $phpunitXmlPath, $replacer);
    }

    public function test_it_replaces_bootstrap_file()
    {
        $xml = $this->configuration->getXml();

        $value = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $this->assertSame($this->pathToProject . '/app/autoload2.php', $value);
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
}
