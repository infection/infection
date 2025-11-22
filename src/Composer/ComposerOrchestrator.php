<?php

declare(strict_types=1);

namespace Infection\Composer;

use Composer\Semver\Semver;
use Fidry\Console\IO;
use Fidry\FileSystem\FileSystem;
use Humbug\PhpScoper\Symbol\SymbolsRegistry;
use Infection\Composer\Throwable\IncompatibleComposerVersion;
use Infection\Composer\Throwable\UndetectableComposerVersion;
use KevinGH\Box\NotInstantiable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use function sprintf;
use function trim;
use const PHP_EOL;

/**
 * This file is taken from the `box-project/box` project.
 *
 *   (c) 2013 Kevin Herrera <kevin@herrera.io>
 *            Théo Fidry <theo.fidry@gmail.com>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 *  of the Software, and to permit persons to whom the Software is furnished to do
 *  so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @private
 */
final class ComposerOrchestrator
{
    use NotInstantiable;

    public const SUPPORTED_VERSION_CONSTRAINTS = '^2.2.0';

    private string $detectedVersion;

    public static function create(): self
    {
        return new self(
            ComposerProcessFactory::create(io: IO::createNull()),
            new NullLogger(),
            new FileSystem(),
        );
    }

    public function __construct(
        private readonly ComposerProcessFactory $processFactory,
        private readonly LoggerInterface $logger,
        private readonly FileSystem $fileSystem,
    ) {
    }

    /**
     * @return string The vendor-dir directory path relative to its composer.json.
     */
    public function getVendorDir(): string
    {
        $vendorDirProcess = $this->processFactory->getVendorDirProcess();

        $this->logger->info($vendorDirProcess->getCommandLine());

        $vendorDirProcess->run();

        if (false === $vendorDirProcess->isSuccessful()) {
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

        if (false === $dumpAutoloadProcess->isSuccessful()) {
            throw new RuntimeException(
                'Could not dump the autoloader.',
                0,
                new ProcessFailedException($dumpAutoloadProcess),
            );
        }

        $output = $dumpAutoloadProcess->getOutput();
        $errorOutput = $dumpAutoloadProcess->getErrorOutput();

        if ('' !== $output) {
            $this->logger->info(
                'STDOUT output:'.PHP_EOL.$output,
                ['stdout' => $output],
            );
        }

        if ('' !== $errorOutput) {
            $this->logger->info(
                'STDERR output:'.PHP_EOL.$errorOutput,
                ['stderr' => $errorOutput],
            );
        }
    }
}
