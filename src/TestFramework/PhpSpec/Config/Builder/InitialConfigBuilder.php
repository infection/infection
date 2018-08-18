<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Config\Builder;

use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\InitialYamlConfiguration;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
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

    /**
     * @var bool
     */
    private $skipCoverage;

    public function __construct(string $tempDirectory, string $originalYamlConfigPath, bool $skipCoverage)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalYamlConfigPath = $originalYamlConfigPath;
        $this->skipCoverage = $skipCoverage;
    }

    public function build(): string
    {
        $path = $this->buildPath();

        $yamlConfiguration = new InitialYamlConfiguration(
            $this->tempDirectory,
            Yaml::parseFile($this->originalYamlConfigPath),
            $this->skipCoverage
        );

        file_put_contents($path, $yamlConfiguration->getYaml());

        return $path;
    }

    private function buildPath(): string
    {
        return $this->tempDirectory . '/phpspecConfiguration.initial.infection.yml';
    }
}
