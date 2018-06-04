<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
class InfectionConfig
{
    const PROCESS_TIMEOUT_SECONDS = 10;
    const DEFAULT_SOURCE_DIRS = ['.'];
    const DEFAULT_EXCLUDE_DIRS = ['vendor'];
    const CONFIG_FILE_NAME = 'infection.json';
    const POSSIBLE_CONFIG_FILE_NAMES = [
        self::CONFIG_FILE_NAME,
        self::CONFIG_FILE_NAME . '.dist',
    ];

    /**
     * @var \stdClass
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $configLocation;

    public function __construct(\stdClass $config, Filesystem $filesystem, string $configLocation)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->configLocation = $configLocation;
    }

    public function getPhpUnitConfigDir(): string
    {
        if (isset($this->config->phpUnit->configDir)) {
            return $this->configLocation . \DIRECTORY_SEPARATOR . $this->config->phpUnit->configDir;
        }

        return $this->configLocation;
    }

    public function getPhpUnitCustomPath(): string
    {
        return $this->config->phpUnit->customPath ?? '';
    }

    public function getProcessTimeout(): int
    {
        return $this->config->timeout ?? self::PROCESS_TIMEOUT_SECONDS;
    }

    public function getSourceDirs(): array
    {
        return $this->config->source->directories ?? self::DEFAULT_SOURCE_DIRS;
    }

    public function getSourceExcludePaths(): array
    {
        $originalExcludedPaths = $this->getExcludes();
        $excludedPaths = [];

        foreach ($originalExcludedPaths as $originalExcludedPath) {
            if (strpos($originalExcludedPath, '*') === false) {
                $excludedPaths[] = $originalExcludedPath;
            } else {
                $excludedPaths = array_merge(
                    $excludedPaths,
                    $this->getExcludedDirsByPattern($originalExcludedPath)
                );
            }
        }

        return $excludedPaths;
    }

    private function getExcludes(): array
    {
        if (isset($this->config->source->excludes) && is_array($this->config->source->excludes)) {
            return $this->config->source->excludes;
        }

        return self::DEFAULT_EXCLUDE_DIRS;
    }

    public function getLogsTypes(): array
    {
        return (array) ($this->config->logs ?? []);
    }

    private function getExcludedDirsByPattern(string $originalExcludedDir)
    {
        $excludedDirs = [];
        $srcDirs = $this->getSourceDirs();

        foreach ($srcDirs as $srcDir) {
            $unpackedPaths = glob(
                sprintf('%s/%s', $srcDir, $originalExcludedDir),
                GLOB_ONLYDIR
            );

            if ($unpackedPaths) {
                $excludedDirs = array_merge(
                    $excludedDirs,
                    array_map(
                        function ($excludeDir) use ($srcDir) {
                            return ltrim(
                                substr_replace($excludeDir, '', 0, strlen($srcDir)),
                                \DIRECTORY_SEPARATOR
                            );
                        },
                        $unpackedPaths
                    )
                );
            }
        }

        return $excludedDirs;
    }

    public function getTmpDir(): string
    {
        if (empty($this->config->tmpDir)) {
            return sys_get_temp_dir();
        }

        $tmpDir = $this->config->tmpDir;

        if ($this->filesystem->isAbsolutePath($tmpDir)) {
            return $tmpDir;
        }

        return sprintf('%s/%s', $this->configLocation, $tmpDir);
    }

    public function getMutatorsConfiguration(): array
    {
        return (array) ($this->config->mutators ?? []);
    }

    public function getBootstrap(): string
    {
        return $this->config->bootstrap ?? '';
    }

    public function getTestFramework(): string
    {
        return $this->config->testFramework ?? 'phpunit';
    }
}
