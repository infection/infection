<?php

declare(strict_types=1);


namespace Infection\TestFramework\Codeception\Config;

use Webmozart\Assert\Assert;

/**
 * @internal
 */
abstract class AbstractYamlConfiguration
{
    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var array
     */
    protected $originalConfig;

    /**
     * @var bool
     */
    protected $skipCoverage;

    public function __construct(string $tmpDir, string $projectDir, array $originalConfig, bool $skipCoverage)
    {
        $this->tmpDir = $tmpDir;
        $this->projectDir = $projectDir;
        $this->originalConfig = $originalConfig;
        $this->skipCoverage = $skipCoverage;
    }

    abstract public function getYaml(): string;

    protected function getPathToProjectDir(): string
    {
        /** @var string $projectDir */
        $projectDir = realpath($this->projectDir);
        /** @var string $tmpDir */
        $tmpDir = realpath($this->tmpDir);

        $projectDirParts = explode(DIRECTORY_SEPARATOR, $projectDir);
        $tempDirParts = explode(DIRECTORY_SEPARATOR, $tmpDir);

        while (count($projectDirParts) > 0 && count($tempDirParts) > 0 && strcmp($projectDirParts[0], $tempDirParts[0]) === 0) {
            array_shift($projectDirParts);
            array_shift($tempDirParts);
        }

        $pathToProjectDir = str_repeat('../', count($tempDirParts)) . implode('/', $projectDirParts) . '/';

        return $pathToProjectDir;
    }

    protected function updatePaths(array $config, string $projectPath): array
    {
        $returnConfig = [];

        foreach($config as $key => $value) {
            if (is_array($value)) {
                $value = $this->updatePaths($value, $projectPath);
            } elseif (is_string($value) && file_exists($projectPath . $value)) {
                $value = $projectPath . $value;
            }

            $returnConfig[$key] = $value;
        }

        return $returnConfig;
    }
}
