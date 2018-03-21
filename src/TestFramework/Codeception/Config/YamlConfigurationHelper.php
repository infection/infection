<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Config;

use Symfony\Component\Yaml\Yaml;

class YamlConfigurationHelper
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var string
     */
    private $originalConfig;

    /**
     * @var string[]
     */
    private $srcDirs;

    public function __construct(string $tempDir, string $projectDir, string $originalConfig, array $srcDirs = [])
    {
        $this->tempDir = substr($tempDir, -1) === DIRECTORY_SEPARATOR ? substr($tempDir, 0, -1) : $tempDir;
        $this->originalConfig = $originalConfig;
        $this->projectDir = substr($projectDir, -1) === DIRECTORY_SEPARATOR ? substr($projectDir, 0, -1) : $projectDir;
        $this->srcDirs = $srcDirs;
    }

    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getTransformedConfig(string $outputDir = '.', bool $coverageEnabled = true): string
    {
        $projectDirParts = explode(DIRECTORY_SEPARATOR, realpath($this->projectDir));
        $tempDirParts = explode(DIRECTORY_SEPARATOR, realpath($this->tempDir));

        while (count($projectDirParts) > 0 && count($tempDirParts) > 0 && strcmp($projectDirParts[0], $tempDirParts[0]) === 0) {
            array_shift($projectDirParts);
            array_shift($tempDirParts);
        }

        $pathToProjectDir = str_repeat('../', count($tempDirParts)) . implode('/', $projectDirParts) . '/';

        $config = Yaml::parse($this->originalConfig);
        $config['paths'] = [
            'tests' => $pathToProjectDir . ($config['paths']['tests'] ?? 'tests'),
            'output' => $this->tempDir . '/' . $outputDir,
            'data' => $pathToProjectDir . ($config['paths']['data'] ?? 'tests/_data'),
            'support' => $pathToProjectDir . ($config['paths']['support'] ?? 'tests/_support'),
            'envs' => $pathToProjectDir . ($config['paths']['envs'] ?? 'tests/_envs'),
        ];
        $config['coverage'] = [
            'enabled' => $coverageEnabled,
            'include' => $coverageEnabled ? array_map(
                function ($dir) use ($pathToProjectDir) {
                    return $pathToProjectDir . $dir . '/*';
                },
                $this->srcDirs
            ) : [],
            'exclude' => [],
        ];

        return Yaml::dump($config);
    }
}
