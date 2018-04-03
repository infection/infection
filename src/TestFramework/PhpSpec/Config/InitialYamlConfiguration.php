<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Config;

use Infection\TestFramework\Coverage\CodeCoverageData;
use Symfony\Component\Yaml\Yaml;

final class InitialYamlConfiguration extends AbstractYamlConfiguration
{
    /**
     * @var bool
     */
    private $skipCoverage;

    public function __construct(string $tmpDir, array $parsedYaml, bool $skipCoverage)
    {
        parent::__construct($tmpDir, $parsedYaml);

        $this->skipCoverage = $skipCoverage;
    }

    /**
     * @var string
     */
    protected $originalYamlConfigPath;

    public function getYaml(): string
    {
        if ($this->skipCoverage) {
            $this->removeCoverageExtension($this->parsedYaml);
        } else {
            if (!$this->hasCodeCoverageExtension($this->parsedYaml)) {
                throw new NoCodeCoverageException("No code coverage Extension detected for PhpSpec. \nWithout code coverage, running Infection is not useful.");
            }

            $this->updateCodeCoveragePath($this->parsedYaml);
        }

        return Yaml::dump($this->parsedYaml);
    }

    private function updateCodeCoveragePath(array &$parsedYaml)
    {
        foreach ($parsedYaml['extensions'] as $extensionName => &$options) {
            if (!$this->isCodeCoverageExtension($extensionName)) {
                continue;
            }

            $options['format'] = ['xml'];
            $options['output'] = [
                'xml' => $this->tempDirectory . '/' . CodeCoverageData::PHP_SPEC_COVERAGE_DIR,
            ];
        }
        unset($options);
    }

    private function removeCoverageExtension(array &$parsedYaml)
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
