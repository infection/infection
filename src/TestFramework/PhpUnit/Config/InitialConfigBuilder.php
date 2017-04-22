<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\Finder\Locator;
use Infection\TestFramework\Config\ConfigBuilder ;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class InitialConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $tempDirectory;
    /**
     * @var string
     */
    private $originalXmlConfigPath;
    /**
     * @var PathReplacer
     */
    private $pathReplacer;

    public function __construct(string $tempDirectory, string $originalXmlConfigPath, PathReplacer $pathReplacer)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalXmlConfigPath = $originalXmlConfigPath;
        $this->pathReplacer = $pathReplacer;
    }

    public function build() : string
    {
        $path = $this->buildPath();

        $xmlConfigurationFileStrategy = new InitialXmlConfiguration(
            $this->tempDirectory,
            $this->originalXmlConfigPath,
            $this->pathReplacer
        );

        file_put_contents($path, $xmlConfigurationFileStrategy->getXml());

        return $path;
    }

    private function buildPath() : string
    {
        return $this->tempDirectory . '/phpunitConfiguration.initial.infection.xml';
    }
}