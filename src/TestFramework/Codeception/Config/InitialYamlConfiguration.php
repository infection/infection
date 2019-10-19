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
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class InitialYamlConfiguration extends AbstractYamlConfiguration
{
    /**
     * @var string[]
     */
    private $srcDirs;

    /**
     * @var bool
     */
    private $skipCoverage;

    public function __construct(string $tmpDir, string $projectDir, array $originalConfig, bool $skipCoverage, array $srcDirs, Filesystem $filesystem)
    {
        parent::__construct($tmpDir, $projectDir, $originalConfig, $filesystem);

        $this->srcDirs = $srcDirs;
        $this->skipCoverage = $skipCoverage;
    }

    public function getYaml(): string
    {
        $relativeFromTmpDirPathToProjectDir = $this->getRelativeFromTmpDirPathToProjectDir();
        $config = $this->originalConfig;
        /** @var string $projectDirRealPath */
        $projectDirRealPath = realpath($this->projectDir);

        $config['paths'] = $this->updatePaths($config['paths'], $relativeFromTmpDirPathToProjectDir, $projectDirRealPath);

        $config['paths'] = [
            'tests' => $config['paths']['tests'] ?? $relativeFromTmpDirPathToProjectDir . 'tests',
            'output' => $this->tmpDir,
            'data' => $config['paths']['data'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_data',
            'support' => $config['paths']['support'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_support',
            'envs' => $config['paths']['envs'] ?? $relativeFromTmpDirPathToProjectDir . 'tests/_envs',
        ];

        if (\array_key_exists('modules', $config)) {
            $config['modules'] = $this->updatePaths($config['modules'], $relativeFromTmpDirPathToProjectDir, $projectDirRealPath);
        }

        if (\array_key_exists('suites', $config)) {
            $config['suites'] = $this->updatePaths($config['suites'], $relativeFromTmpDirPathToProjectDir, $projectDirRealPath);
        }

        if ($this->skipCoverage) {
            $config['coverage']['enabled'] = false;
        } else {
            $config['coverage'] = $this->prepareCoverageConfig($config, $relativeFromTmpDirPathToProjectDir);
        }

        // run the tests in a random order to make sure we can do mutation testing with a subset of tests
        $config['settings'] = array_merge(
            $config['settings'] ?? [],
            ['shuffle' => true]
        );

        return Yaml::dump($config);
    }

    /**
     * @param array<string, mixed> $fullConfig
     *
     * @return array<string, mixed>
     */
    private function prepareCoverageConfig(array $fullConfig, string $relativeFromTmpDirPathToProjectDir): array
    {
        $coverage = array_merge($fullConfig['coverage'] ?? [], ['enabled' => true]);

        if (\array_key_exists('include', $coverage)) {
            $coverage['include'] = $this->prependWithRelativePathPrefix($coverage['include'], $relativeFromTmpDirPathToProjectDir);
        } else {
            // get all `srcDirs` from `infection.json` as a whitelist for coverage
            $coverage['include'] = array_map(
                static function ($dir) use ($relativeFromTmpDirPathToProjectDir) {
                    return $relativeFromTmpDirPathToProjectDir . trim($dir, '/') . '/*.php';
                },
                $this->srcDirs
            );
        }

        if (\array_key_exists('exclude', $coverage)) {
            $coverage['exclude'] = $this->prependWithRelativePathPrefix($coverage['exclude'], $relativeFromTmpDirPathToProjectDir);
        }

        return $coverage;
    }

    /**
     * @param array<int, string> $dirs
     *
     * @return array<int, string>
     */
    private function prependWithRelativePathPrefix(array $dirs, string $relativeFromTmpDirPathToProjectDir): array
    {
        return array_map(
            static function ($dir) use ($relativeFromTmpDirPathToProjectDir) {
                return $relativeFromTmpDirPathToProjectDir . ltrim($dir, '/');
            },
            $dirs
        );
    }
}
