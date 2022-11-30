<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config;

use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\PhpSpecAdapter;
use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Yaml;
final class InitialYamlConfiguration extends AbstractYamlConfiguration
{
    private bool $skipCoverage;
    public function __construct(string $tmpDir, array $parsedYaml, bool $skipCoverage)
    {
        parent::__construct($tmpDir, $parsedYaml);
        $this->skipCoverage = $skipCoverage;
    }
    public function getYaml() : string
    {
        if ($this->skipCoverage) {
            $this->removeCoverageExtension($this->parsedYaml);
        } else {
            if (!$this->hasCodeCoverageExtension($this->parsedYaml)) {
                throw NoCodeCoverageException::fromTestFramework('PhpSpec');
            }
            $this->updateCodeCoveragePath($this->parsedYaml);
        }
        return Yaml::dump($this->parsedYaml);
    }
    private function updateCodeCoveragePath(array &$parsedYaml) : void
    {
        foreach ($parsedYaml['extensions'] as $extensionName => &$options) {
            if (!$this->isCodeCoverageExtension($extensionName)) {
                continue;
            }
            $options['format'] = ['xml'];
            $options['output'] = ['xml' => $this->tempDirectory . '/' . PhpSpecAdapter::COVERAGE_DIR];
        }
        unset($options);
    }
    private function removeCoverageExtension(array &$parsedYaml) : void
    {
        foreach ($parsedYaml['extensions'] as $extensionName => &$options) {
            if (!$this->isCodeCoverageExtension($extensionName)) {
                continue;
            }
            unset($parsedYaml['extensions'][$extensionName]);
        }
        unset($options);
    }
}
