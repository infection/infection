<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\Config\ConfigBuilder ;

class InitialConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $tempDirectory;

    public function __construct(string $tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }

    public function build() : string
    {
        $path = $this->buildPath();

        $xmlConfigurationFileStrategy = new InitialXmlConfiguration($this->tempDirectory);

        file_put_contents($path, $xmlConfigurationFileStrategy->getXml());

        return $path;
    }

    private function buildPath() : string
    {
        return $this->tempDirectory . '/phpunitConfiguration.initial.infection.xml';
    }
}