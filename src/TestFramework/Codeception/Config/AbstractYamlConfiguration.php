<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
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

namespace Infection\TestFramework\Codeception\Config;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
abstract class AbstractYamlConfiguration
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

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
     * @param array<string, mixed> $originalConfig
     */
    public function __construct(string $tmpDir, string $projectDir, array $originalConfig, Filesystem $filesystem)
    {
        $this->tmpDir = $tmpDir;
        $this->projectDir = $projectDir;
        $this->originalConfig = $originalConfig;
        $this->filesystem = $filesystem;
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
     */
    protected function getRelativeFromTmpDirPathToProjectDir(): string
    {
        return $this->filesystem->makePathRelative($this->projectDir, $this->tmpDir);
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    protected function updatePaths(array $config, string $relativeFromTmpDirPathToProjectDir, string $projectDirRealPath): array
    {
        $returnConfig = [];

        foreach ($config as $key => $value) {
            if (\is_array($value)) {
                $value = $this->updatePaths($value, $relativeFromTmpDirPathToProjectDir, $projectDirRealPath);
            } elseif (\is_string($value) && file_exists(sprintf('%s/%s', $projectDirRealPath, $value))) {
                $value = $relativeFromTmpDirPathToProjectDir . $value;
            }

            $returnConfig[$key] = $value;
        }

        return $returnConfig;
    }
}
