<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Config;

use Infection\TestFramework\Coverage\CodeCoverageData;

abstract class AbstractYamlConfiguration
{
    /**
     * @var string
     */
    protected $tempDirectory;

    /**
     * @var array
     */
    protected $parsedYaml;

    public function __construct(string $tempDirectory, array $parsedYaml)
    {
        $this->tempDirectory = $tempDirectory;
        $this->parsedYaml = $parsedYaml;
    }

    abstract public function getYaml(): string;

    protected function isCodeCoverageExtension(string $extensionName): bool
    {
        return strpos($extensionName, 'CodeCoverage') !== false;
    }

    protected function updateCodeCoveragePath(array &$parsedYaml)
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

    protected function hasCodeCoverageExtension(array $parsedYaml): bool
    {
        if (!array_key_exists('extensions', $parsedYaml)) {
            return false;
        }

        foreach ($parsedYaml['extensions'] as $extensionName => $options) {
            if ($this->isCodeCoverageExtension($extensionName)) {
                return true;
            }
        }

        return false;
    }
}
