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

    public function __construct(string $tmpDir, string $projectDir, array $originalConfig)
    {
        $this->tmpDir = $tmpDir;
        $this->projectDir = $projectDir;
        $this->originalConfig = $originalConfig;
    }

    abstract public function getYaml(): string;

    /**
     * Codeception does not support absolute URLs in the config file: codeception.yml
     *
     * @see https://github.com/Codeception/Codeception/issues/5642
     *
     * All paths in the config are related to `codeception.yml`, that's why when Infection
     * saves custom `codeception.yml` files in the `tmpDir`, we have to build relative
     * URLs from `tmpDir` back to the `projectDir`.
     *
     * Example:
     *     project dir: /path/to/project-dir
     *     temp dir: /tmp/infection
     *     original path in `/path/to/project-dir/codeception.yml`: tests
     *     relative path in `/tmp/infection/custom-codeception.yml`: ../../path/to/project-dir/tests
     *
     * @return string
     */
    protected function getRelativeFromTmpDirPathToProjectDir(): string
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

        $pathToProjectDir = rtrim(str_repeat('../', count($tempDirParts)) . implode('/', $projectDirParts), '/') . '/';

        return $pathToProjectDir;
    }

    protected function updatePaths(array $config, string $relativeFromTmpDirPathToProjectDir, string $projectDirRealPath): array
    {
        $returnConfig = [];

        foreach($config as $key => $value) {
            if (is_array($value)) {
                $value = $this->updatePaths($value, $relativeFromTmpDirPathToProjectDir, $projectDirRealPath);
            } elseif (is_string($value) && file_exists(sprintf('%s/%s', $projectDirRealPath, $value))) {
                $value = $relativeFromTmpDirPathToProjectDir . $value;
            }

            $returnConfig[$key] = $value;
        }

        return $returnConfig;
    }
}
