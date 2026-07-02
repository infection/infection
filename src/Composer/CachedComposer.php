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

/**
 * @internal
 */
final class CachedComposer implements Composer
{
    private ?string $version = null;

    private bool $isVersionChecked = false;

    /** @var non-empty-string|null */
    private ?string $vendorDir = null;

    private bool $isVendorDirRetrieved = false;

    /** @var non-empty-string|null */
    private ?string $binDir = null;

    private bool $isBinDirRetrieved = false;

    public function __construct(
        private readonly Composer $decoratedComposer,
    ) {
    }

    /**
     * @throws UndetectableComposerVersion
     */
    public function getVersion(): string
    {
        return $this->version ??= $this->decoratedComposer->getVersion();
    }

    /**
     * @throws UndetectableComposerVersion
     * @throws IncompatibleComposerVersion
     */
    public function checkVersion(): void
    {
        if ($this->isVersionChecked) {
            return;
        }

        $this->decoratedComposer->checkVersion();

        $this->isVersionChecked = true;
    }

    /**
     * @return non-empty-string|null The vendor-dir directory path relative to its composer.json.
     */
    public function getVendorDir(): ?string
    {
        if ($this->isVendorDirRetrieved) {
            return $this->vendorDir;
        }

        $this->vendorDir = $this->decoratedComposer->getVendorDir();
        $this->isVendorDirRetrieved = true;

        return $this->vendorDir;
    }

    /**
     * @return non-empty-string|null The Composer bin-dir path.
     */
    public function getBinDir(): ?string
    {
        if ($this->isBinDirRetrieved) {
            return $this->binDir;
        }

        $this->binDir = $this->decoratedComposer->getBinDir();
        $this->isBinDirRetrieved = true;

        return $this->binDir;
    }

    /**
     * @throws ComposerPackageInstallationFailed
     */
    public function requireDevPackage(string $package): void
    {
        $this->decoratedComposer->requireDevPackage($package);
    }
}
