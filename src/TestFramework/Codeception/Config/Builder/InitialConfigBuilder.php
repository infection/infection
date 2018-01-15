<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;
use Infection\TestFramework\Codeception\Config\InitialYamlConfiguration;
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

    public function build(): string
    {
        $path = $this->buildPath();

        $yamlConfiguration = new InitialYamlConfiguration(
            $this->tempDirectory,
            Yaml::parse(file_get_contents($this->originalYamlConfigPath))
        );

        file_put_contents($path, $yamlConfiguration->getYaml());

        return $path;
    }

    private function buildPath(): string
    {
        return $this->tempDirectory . '/phpspecConfiguration.initial.infection.yml';
    }
}
