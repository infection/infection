<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Config\Builder;

use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\InitialYamlConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Symfony\Component\Yaml\Yaml;

class InitialConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $tempDirectory;
    /**
     * @var string
     */
    private $originalYamlConfigPath;

    public function __construct(string $tempDirectory, string $originalYamlConfigPath)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalYamlConfigPath = $originalYamlConfigPath;
    }

    public function build() : string
    {
        $path = $this->buildPath();

        $yamlConfiguration = new InitialYamlConfiguration(
            $this->tempDirectory,
            Yaml::parse(file_get_contents($this->originalYamlConfigPath))
        );

        file_put_contents($path, $yamlConfiguration->getYaml());

        return $path;
    }

    private function buildPath() : string
    {
        return $this->tempDirectory . '/phpspecConfiguration.initial.infection.yml';
    }
}