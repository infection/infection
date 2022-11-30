<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config;

use function array_merge;
use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Yaml;
final class MutationYamlConfiguration extends AbstractYamlConfiguration
{
    private string $customAutoloadFilePath;
    public function __construct(string $tmpDir, array $parsedYaml, string $customAutoloadFilePath)
    {
        parent::__construct($tmpDir, $parsedYaml);
        $this->customAutoloadFilePath = $customAutoloadFilePath;
    }
    public function getYaml() : string
    {
        $config = $this->removeCodeCoverageExtension($this->parsedYaml);
        $config = $this->setCustomAutoLoaderPath($config);
        return Yaml::dump($config);
    }
    private function removeCodeCoverageExtension(array $parsedYaml) : array
    {
        if (!$this->hasCodeCoverageExtension($parsedYaml)) {
            return $parsedYaml;
        }
        $filteredExtensions = [];
        foreach ($parsedYaml['extensions'] as $extensionName => $options) {
            if (!$this->isCodeCoverageExtension($extensionName)) {
                $filteredExtensions[$extensionName] = $options;
            }
        }
        return array_merge($parsedYaml, ['extensions' => $filteredExtensions]);
    }
    private function setCustomAutoLoaderPath(array $config) : array
    {
        return ['bootstrap' => $this->customAutoloadFilePath] + $config;
    }
}
