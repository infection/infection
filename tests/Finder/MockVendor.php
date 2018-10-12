<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Finder;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class MockVendor
{
    public const VENDOR = 'phptester';
    public const PACKAGE = 'awesome-php-tester';

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $packageScript;

    /**
     * @var string
     */
    private $scriptPath;

    /**
     * @var string
     */
    private $vendorBinDir;

    /**
     * @var string
     */
    private $vendorBinLink;

    /**
     * @var string
     */
    private $vendorBinBat;

    public function __construct(string $tmpDir, Filesystem $fileSystem)
    {
        $this->tmpDir = $tmpDir;
        $this->fileSystem = $fileSystem;

        $vendorDir = $this->tmpDir . '/vendor';
        $this->vendorBinDir = $vendorDir . '/bin';

        $binaryPath = self::VENDOR . '/' . self::PACKAGE . '/bin';
        $scriptDir = $vendorDir . '/' . $binaryPath;

        $this->fileSystem->mkdir([
            $this->vendorBinDir,
            $scriptDir,
        ]);

        // The package main script
        $this->packageScript = $scriptDir . '/' . self::PACKAGE;
        file_put_contents($this->packageScript, "#!/usr/bin/env php\n<?php\n");

        // The relative path to the main script
        $this->scriptPath = $binaryPath . '/' . self::PACKAGE;

        $this->vendorBinLink = $this->vendorBinDir . '/' . self::PACKAGE;
        $this->vendorBinBat = $this->vendorBinLink . '.bat';
    }

    public function setUpPlatformTest(): void
    {
        $this->emptyVendorBin();

        if ('\\' === \DIRECTORY_SEPARATOR) {
            // Use an empty batch script to disable finding the main script
            file_put_contents($this->vendorBinBat, '@ECHO OFF');
        } else {
            // Mimic a symlink
            file_put_contents($this->vendorBinLink, "#!/usr/bin/env php\n<?php\n");
        }
    }

    public function setUpComposerBatchTest(): void
    {
        $this->emptyVendorBin();

        // Use a valid batch script to test finding main script
        $code = $this->getComposerBatProxy($this->scriptPath);
        file_put_contents($this->vendorBinBat, $code);
    }

    public function setUpProjectBatchTest(): void
    {
        $this->emptyVendorBin();

        // Use a valid batch script to test finding main script
        $code = $this->getProjectBatProxy($this->scriptPath);
        file_put_contents($this->vendorBinBat, $code);
    }

    public function getVendorBinDir(): string
    {
        return $this->vendorBinDir;
    }

    public function getPackageScript(): string
    {
        return $this->packageScript;
    }

    public function getVendorBinBat(): string
    {
        return $this->vendorBinBat;
    }

    public function getVendorBinLink(): string
    {
        return $this->vendorBinLink;
    }

    private function emptyVendorBin(): void
    {
        $files = array_filter(
            [$this->vendorBinLink, $this->vendorBinBat],
            'file_exists'
        );

        $this->fileSystem->remove($files);
    }

    private function getComposerBatProxy($binaryPath)
    {
        // As per Composer proxy code (BinaryInstaller::generateWindowsProxyCode)
        $code = [
            '@ECHO OFF',
            'setlocal DISABLEDELAYEDEXPANSION',
            'SET BIN_TARGET=%~dp0../' . $binaryPath,
            'php "%BIN_TARGET%" %*',
        ];

        return implode(PHP_EOL, $code) . PHP_EOL;
    }

    private function getProjectBatProxy($binaryPath)
    {
        // Basic proxy
        $code = [
            '@ECHO OFF',
            'php "%~dp0../' . $binaryPath . '" %*',
        ];

        return implode(PHP_EOL, $code) . PHP_EOL;
    }
}
