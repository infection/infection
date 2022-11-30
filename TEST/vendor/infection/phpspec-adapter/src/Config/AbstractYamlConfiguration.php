<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config;

use function array_key_exists;
use function strpos;
abstract class AbstractYamlConfiguration
{
    protected string $tempDirectory;
    protected array $parsedYaml;
    public function __construct(string $tmpDir, array $parsedYaml)
    {
        $this->tempDirectory = $tmpDir;
        $this->parsedYaml = $parsedYaml;
    }
    public abstract function getYaml() : string;
    protected function isCodeCoverageExtension(string $extensionName) : bool
    {
        return strpos($extensionName, 'CodeCoverage') !== \false;
    }
    protected function hasCodeCoverageExtension(array $parsedYaml) : bool
    {
        if (!array_key_exists('extensions', $parsedYaml)) {
            return \false;
        }
        foreach ($parsedYaml['extensions'] as $extensionName => $options) {
            if ($this->isCodeCoverageExtension($extensionName)) {
                return \true;
            }
        }
        return \false;
    }
}
