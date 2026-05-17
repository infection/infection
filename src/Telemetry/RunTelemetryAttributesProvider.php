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

namespace Infection\Telemetry;

use function basename;
use function class_exists;
use Composer\InstalledVersions;
use function getenv;
use Infection\Configuration\Configuration;
use Infection\Console\Application;
use Infection\FileSystem\FileSystem;
use Infection\Process\ShellCommandLineExecutor;
use function is_array;
use function is_string;
use OutOfBoundsException;
use Phar;
use Safe\Exceptions\JsonException;
use function Safe\json_decode;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use function trim;

/**
 * @internal
 */
final readonly class RunTelemetryAttributesProvider
{
    public const string INFECTION_PROJECT_NAME = 'INFECTION_PROJECT_NAME';

    public function __construct(
        private Configuration $configuration,
        private FileSystem $fileSystem,
        private ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {
    }

    /**
     * @return array<non-empty-string, bool|int|float|string>
     */
    public function provide(): array
    {
        $attributes = [
            'infection.project.name' => $this->getProjectName(),
            'infection.project.dir' => $this->configuration->projectDirectory,
            'infection.config.path' => $this->getConfigurationPath(),
            'infection.version' => self::getVersion(),
            'infection.distribution' => self::getDistribution(),
            'infection.thread.count' => $this->configuration->threadCount,
            'infection.initial_tests.skipped' => $this->configuration->skipInitialTests,
            'infection.initial_static_analysis.skipped' => !$this->configuration->isStaticAnalysisEnabled(),
        ];

        $gitSha = $this->getGitSha();

        if ($gitSha !== null) {
            $attributes['infection.git.sha'] = $gitSha;
        }

        return $attributes;
    }

    private function getProjectName(): string
    {
        $projectName = trim((string) getenv(self::INFECTION_PROJECT_NAME));

        if ($projectName !== '') {
            return $projectName;
        }

        $composerPackageName = $this->getRootComposerPackageName();

        if ($composerPackageName !== null) {
            return $composerPackageName;
        }

        return basename($this->configuration->projectDirectory);
    }

    private function getRootComposerPackageName(): ?string
    {
        $composerJsonPath = $this->configuration->projectDirectory . '/composer.json';

        if (!$this->fileSystem->isReadableFile($composerJsonPath)) {
            return null;
        }

        try {
            $composerJson = json_decode($this->fileSystem->readFile($composerJsonPath), true);
        } catch (JsonException) {
            return null;
        }

        if (!is_array($composerJson) || !isset($composerJson['name']) || !is_string($composerJson['name'])) {
            return null;
        }

        $name = trim($composerJson['name']);

        return $name === '' ? null : $name;
    }

    private function getConfigurationPath(): string
    {
        $projectDirectory = Path::canonicalize($this->configuration->projectDirectory);
        $configurationPathname = Path::canonicalize($this->configuration->configurationPathname);

        if (Path::isBasePath($projectDirectory, $configurationPathname)) {
            return Path::makeRelative($configurationPathname, $projectDirectory);
        }

        return $configurationPathname;
    }

    private function getGitSha(): ?string
    {
        try {
            $sha = $this->shellCommandLineExecutor->execute([
                'git',
                '-C',
                $this->configuration->projectDirectory,
                'rev-parse',
                'HEAD',
            ]);
        } catch (ProcessException) {
            return null;
        }

        $sha = trim($sha);

        return $sha === '' ? null : $sha;
    }

    private static function getVersion(): string
    {
        if (!class_exists(InstalledVersions::class)) {
            return 'unknown';
        }

        try {
            return (string) InstalledVersions::getPrettyVersion(Application::PACKAGE_NAME);
        } catch (OutOfBoundsException) {
            return 'not-installed';
        }
    }

    private static function getDistribution(): string
    {
        return Phar::running(false) === ''
            ? 'source'
            : 'phar';
    }
}
