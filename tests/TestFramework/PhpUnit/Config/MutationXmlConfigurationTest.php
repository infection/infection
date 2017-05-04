<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config;

use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\MutationXmlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class MutationXmlConfigurationTest extends AbstractXmlConfiguration
{
    private $customAutoloadConfigPath = '/custom/path/autoload.php';

    protected function getConfigurationObject()
    {
        $phpunitXmlPath = __DIR__ . '/../../../Files/phpunit/phpunit.xml';

        $replacer = new PathReplacer(new Locator($this->pathToProject));

        return new MutationXmlConfiguration($this->tempDir, $phpunitXmlPath, $replacer, $this->customAutoloadConfigPath);
    }

    public function test_it_sets_custom_autoloader()
    {
        $xml = $this->configuration->getXml();

        $value = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $this->assertSame($this->customAutoloadConfigPath, $value);
    }

    public function test_it_sets_stop_on_failure_flag()
    {
        $xml = $this->configuration->getXml();

        $value = $this->queryXpath($xml, '/phpunit/@stopOnFailure')[0]->nodeValue;

        $this->assertSame('true', $value);
    }

    public function test_it_replaces_test_suite_directory_wildcard_from_another_folder()
    {
        $phpUnitConfigDir = __DIR__ . '/../../../Files/phpunit/project-path/app';
        $phpunitXmlPath = $phpUnitConfigDir . '/phpunit.xml';

        $replacer = new PathReplacer(new Locator($this->pathToProject), $phpUnitConfigDir);

        $configuration = new MutationXmlConfiguration($this->tempDir, $phpunitXmlPath, $replacer, $this->customAutoloadConfigPath);

        $xml = $configuration->getXml();

        /** @var \DOMNodeList $directories */
        $directories = $this->queryXpath($xml, '/phpunit/testsuites/testsuite/directory');

        $this->assertSame(2, $directories->length);
        $this->assertSame($this->pathToProject . '/AnotherBundle', $directories[0]->nodeValue);
        $this->assertSame($this->pathToProject . '/SomeBundle', $directories[1]->nodeValue);
    }
}
