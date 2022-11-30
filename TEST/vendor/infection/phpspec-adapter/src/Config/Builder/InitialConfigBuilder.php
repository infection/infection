<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config\Builder;

use function file_put_contents;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config\InitialYamlConfiguration;
use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Yaml;
class InitialConfigBuilder
{
    private string $tempDirectory;
    private string $originalYamlConfigPath;
    private bool $skipCoverage;
    public function __construct(string $tempDirectory, string $originalYamlConfigPath, bool $skipCoverage)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalYamlConfigPath = $originalYamlConfigPath;
        $this->skipCoverage = $skipCoverage;
    }
    public function build(string $version) : string
    {
        $path = $this->buildPath();
        $yamlConfiguration = new InitialYamlConfiguration($this->tempDirectory, Yaml::parseFile($this->originalYamlConfigPath), $this->skipCoverage);
        file_put_contents($path, $yamlConfiguration->getYaml());
        return $path;
    }
    private function buildPath() : string
    {
        return $this->tempDirectory . '/phpspecConfiguration.initial.infection.yml';
    }
}
