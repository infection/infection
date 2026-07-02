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

/*
 * This file is part of the box project.
 *
 * (c) Kevin Herrera <kevin@herrera.io>
 *     Théo Fidry <theo.fidry@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Infection\Composer\Box;

use Composer\Semver\Semver;
use Fidry\Console\IO;
use Fidry\FileSystem\FileSystem;
use Humbug\PhpScoper\Symbol\SymbolsRegistry;
use KevinGH\Box\Composer\AutoloadDumper;
use KevinGH\Box\Composer\ComposerProcessFactory;
use KevinGH\Box\Composer\Throwable\IncompatibleComposerVersion;
use KevinGH\Box\Composer\Throwable\UndetectableComposerVersion;
use KevinGH\Box\NotInstantiable;
use const PHP_EOL;
use function preg_match;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use function sprintf;
use Symfony\Component\Process\Exception\ProcessFailedException;
use function trim;

/**
 * @private
 */
final class ComposerOrchestrator
{
    use NotInstantiable;

    public const SUPPORTED_VERSION_CONSTRAINTS = '^2.2.0';

    private string $detectedVersion;

    public function __construct(
        private readonly ComposerProcessFactory $processFactory,
        private readonly LoggerInterface $logger,
        private readonly FileSystem $fileSystem,
    ) {
    }

    public static function create(): self
    {
        return new self(
            ComposerProcessFactory::create(io: IO::createNull()),
            new NullLogger(),
            new FileSystem(),
        );
    }

    /**
     * @throws UndetectableComposerVersion
     */
    public function getVersion(): string
    {
        if (isset($this->detectedVersion)) {
            return $this->detectedVersion;
        }

        $getVersionProcess = $this->processFactory->getVersionProcess();

        $this->logger->info($getVersionProcess->getCommandLine());

        $getVersionProcess->run();

        if ($getVersionProcess->isSuccessful() === false) {
            throw UndetectableComposerVersion::forFailedProcess($getVersionProcess);
        }

        $output = $getVersionProcess->getOutput();

        if (preg_match('/Composer version (\S+?) /', $output, $match) !== 1) {
            throw UndetectableComposerVersion::forOutput(
                $getVersionProcess,
                $output,
            );
        }

        $this->detectedVersion = $match[1];

        return $this->detectedVersion;
    }

    /**
     * @throws UndetectableComposerVersion
     * @throws IncompatibleComposerVersion
     */
    public function checkVersion(): void
    {
        $version = $this->getVersion();

        $this->logger->info(
            sprintf(
                'Version detected: %s (Box requires %s)',
                $version,
                self::SUPPORTED_VERSION_CONSTRAINTS,
            ),
        );

        if (!Semver::satisfies($version, self::SUPPORTED_VERSION_CONSTRAINTS)) {
            throw IncompatibleComposerVersion::create($version, self::SUPPORTED_VERSION_CONSTRAINTS);
        }
    }

    /**
     * @param string[] $excludedComposerAutoloadFiles Relative paths of the files that were not scoped hence which need
     *                                                to be configured as loaded to Composer as otherwise they would be
     *                                                autoloaded twice.
     */
    public function dumpAutoload(
        SymbolsRegistry $symbolsRegistry,
        string $prefix,
        bool $excludeDevFiles,
        array $excludedComposerAutoloadFiles,
    ): void {
        $this->dumpAutoloader($excludeDevFiles === true);

        if ($prefix === '') {
            return;
        }

        $vendorDir = $this->getVendorDir();
        $autoloadFile = $vendorDir . '/autoload.php';

        $autoloadContents = AutoloadDumper::generateAutoloadStatements(
            $symbolsRegistry,
            $vendorDir,
            $excludedComposerAutoloadFiles,
            $this->fileSystem->getFileContents($autoloadFile),
        );

        $this->fileSystem->dumpFile($autoloadFile, $autoloadContents);
    }

    /**
     * @return string The vendor-dir directory path relative to its composer.json.
     */
    public function getVendorDir(): string
    {
        $vendorDirProcess = $this->processFactory->getVendorDirProcess();

        $this->logger->info($vendorDirProcess->getCommandLine());

        $vendorDirProcess->run();

        if ($vendorDirProcess->isSuccessful() === false) {
            throw new RuntimeException(
                'Could not retrieve the vendor dir.',
                0,
                new ProcessFailedException($vendorDirProcess),
            );
        }

        return trim($vendorDirProcess->getOutput());
    }

    private function dumpAutoloader(bool $noDev): void
    {
        $dumpAutoloadProcess = $this->processFactory->getDumpAutoloaderProcess($noDev);

        $this->logger->info($dumpAutoloadProcess->getCommandLine());

        $dumpAutoloadProcess->run();

        if ($dumpAutoloadProcess->isSuccessful() === false) {
            throw new RuntimeException(
                'Could not dump the autoloader.',
                0,
                new ProcessFailedException($dumpAutoloadProcess),
            );
        }

        $output = $dumpAutoloadProcess->getOutput();
        $errorOutput = $dumpAutoloadProcess->getErrorOutput();

        if ($output !== '') {
            $this->logger->info(
                'STDOUT output:' . PHP_EOL . $output,
                ['stdout' => $output],
            );
        }

        if ($errorOutput !== '') {
            $this->logger->info(
                'STDERR output:' . PHP_EOL . $errorOutput,
                ['stderr' => $errorOutput],
            );
        }
    }
}
