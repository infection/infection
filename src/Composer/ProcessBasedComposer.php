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

namespace Infection\Composer;

use Infection\Composer\Throwable\ComposerPackageInstallationFailed;
use Infection\Composer\Throwable\IncompatibleComposerVersion;
use Infection\Composer\Throwable\UndetectableComposerVersion;
use function preg_match;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;
use function trim;
use function version_compare;

/**
 * @internal
 */
final readonly class ProcessBasedComposer implements Composer
{
    private const SUPPORTED_VERSION_CONSTRAINTS = '^2.0';

    private const MIN_SUPPORTED_VERSION_CONSTRAINTS_ALLOWED = '2.0.0';

    private const MAX_SUPPORTED_VERSION_CONSTRAINTS_NOT_ALLOWED = '3.0.0';

    public function __construct(
        private ComposerProcessFactory $processFactory,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function getVersion(): string
    {
        $process = $this->processFactory->getVersionProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $this->logProcessError(
                'Could not detect the Composer version.',
                $process,
            );

            throw UndetectableComposerVersion::forFailedProcess($process);
        }

        $output = $process->getOutput();

        if (preg_match(
            '/Composer version (\S+?) /',
            $output,
            $match,
        ) !== 1) {
            $this->logProcessError(
                'Could not determine the Composer version from the Composer output.',
                $process,
            );

            throw UndetectableComposerVersion::forOutput(
                $process,
                $output,
            );
        }

        return $match[1];
    }

    /**
     * @throws UndetectableComposerVersion
     * @throws IncompatibleComposerVersion
     */
    public function checkVersion(): void
    {
        $version = $this->getVersion();

        if (!$this->isSupportedVersion($version)) {
            throw IncompatibleComposerVersion::create(
                $version,
                self::SUPPORTED_VERSION_CONSTRAINTS,
            );
        }
    }

    /**
     * @return non-empty-string|null The vendor-dir directory path relative to its composer.json.
     */
    public function getVendorDir(): ?string
    {
        $process = $this->processFactory->getVendorDirProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $this->logProcessError(
                'Could not detect the Composer vendor dir.',
                $process,
            );

            return null;
        }

        $vendorDir = trim($process->getOutput());

        if ($vendorDir === '') {
            $this->logProcessError(
                'Could not determine the Composer vendor dir from the Composer output.',
                $process,
            );

            return null;
        }

        return $vendorDir;
    }

    /**
     * @return non-empty-string|null The Composer bin-dir path.
     */
    public function getBinDir(): ?string
    {
        $process = $this->processFactory->getBinDirProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $this->logProcessError(
                'Could not detect the Composer bin dir.',
                $process,
            );

            return null;
        }

        $binDir = trim($process->getOutput());

        if ($binDir === '') {
            $this->logProcessError(
                'Could not determine the Composer bin dir from the Composer output.',
                $process,
            );

            return null;
        }

        return $binDir;
    }

    /**
     * @throws ComposerPackageInstallationFailed
     */
    public function requireDevPackage(string $package): void
    {
        $process = $this->processFactory->getRequireDevPackageProcess($package);

        $process->run();

        if (!$process->isSuccessful()) {
            $this->logProcessError(
                'Could not install the Composer package.',
                $process,
            );

            throw ComposerPackageInstallationFailed::forFailedProcess(
                $package,
                $process,
            );
        }
    }

    private function isSupportedVersion(string $version): bool
    {
        return version_compare(
            $version,
            self::MIN_SUPPORTED_VERSION_CONSTRAINTS_ALLOWED,
            '>=',
        )
            && version_compare(
                $version,
                self::MAX_SUPPORTED_VERSION_CONSTRAINTS_NOT_ALLOWED,
                '<',
            );
    }

    private function logProcessError(
        string $message,
        Process $process,
    ): void {
        $this->logger->info(
            $message,
            [
                'command' => $process->getCommandLine(),
                'exit_code' => $process->getExitCode(),
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput(),
            ],
        );
    }
}
