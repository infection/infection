<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config;

use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\InitialXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use function Infection\Tests\normalizePath as p;

class InitialXmlConfigurationTest extends AbstractXmlConfiguration
{
    protected function getConfigurationObject()
    {
        $phpunitXmlPath = __DIR__ . '/../../../Files/phpunit/phpunit.xml';
        $jUnitFilePath = '/path/to/jsunit.xml';

        $replacer = new PathReplacer(new Locator($this->pathToProject));

        return new InitialXmlConfiguration($this->tempDir, $phpunitXmlPath, $replacer, $jUnitFilePath);
    }

    public function test_it_replaces_test_suite_directory_wildcard()
    {
        $xml = $this->configuration->getXml();

        /** @var \DOMNodeList $directories */
        $directories = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/directory');

        $this->assertSame(2, $directories->length);
        $this->assertSame($this->pathToProject . '/AnotherBundle', p($directories[0]->nodeValue));
        $this->assertSame($this->pathToProject . '/SomeBundle', p($directories[1]->nodeValue));
    }

    public function test_it_replaces_bootstrap_file()
    {
        $xml = $this->configuration->getXml();

        $value = p($this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue);

        $this->assertSame($this->pathToProject . '/app/autoload2.php', $value);
    }

    public function test_it_adds_php_logger()
    {
        $xml = $this->configuration->getXml();

        /** @var \DOMNodeList $logEntries */
        $logEntries = $this->queryXpath($xml, '/phpunit/logging/log');

        $this->assertSame(2, $logEntries->length);
        $this->assertSame($this->tempDir . '/coverage-xml', $logEntries[0]->getAttribute('target'));
        $this->assertSame('coverage-xml', $logEntries[0]->getAttribute('type'));
        $this->assertSame('junit', $logEntries[1]->getAttribute('type'));
    }
}
