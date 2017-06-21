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

        return new MutationXmlConfiguration(
            $this->tempDir,
            $phpunitXmlPath,
            $replacer,
            $this->customAutoloadConfigPath,
            []
        );
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

    public function test_it_sets_colors_flag()
    {
        $xml = $this->configuration->getXml();

        $value = $this->queryXpath($xml, '/phpunit/@colors')[0]->nodeValue;

        $this->assertSame('false', $value);
    }
}
