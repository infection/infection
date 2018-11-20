<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Config;

use Infection\TestFramework\TestFrameworkTypes;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
class InfectionConfig
{
    public const PROCESS_TIMEOUT_SECONDS = 10;
    public const DEFAULT_SOURCE_DIRS = ['.'];
    public const DEFAULT_EXCLUDE_DIRS = ['vendor'];
    public const CONFIG_FILE_NAME = 'infection.json';
    public const POSSIBLE_CONFIG_FILE_NAMES = [
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
        if (!isset($this->config->phpUnit->configDir)) {
            return $this->configLocation;
        }

        if ($this->filesystem->isAbsolutePath($this->config->phpUnit->configDir)) {
            return $this->config->phpUnit->configDir;
        }

        return $this->configLocation . \DIRECTORY_SEPARATOR . $this->config->phpUnit->configDir;
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

    public function getLogsTypes(): array
    {
        return (array) ($this->config->logs ?? []);
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
        return $this->config->testFramework ?? TestFrameworkTypes::PHPUNIT;
    }

    public function getInitialTestsPhpOptions(): string
    {
        return $this->config->initialTestsPhpOptions ?? '';
    }

    public function getTestFrameworkOptions(): string
    {
        return $this->config->testFrameworkOptions ?? '';
    }

    private function getExcludes(): array
    {
        if (isset($this->config->source->excludes) && \is_array($this->config->source->excludes)) {
            return $this->config->source->excludes;
        }

        return self::DEFAULT_EXCLUDE_DIRS;
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
                                substr_replace($excludeDir, '', 0, \strlen($srcDir)),
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
}
