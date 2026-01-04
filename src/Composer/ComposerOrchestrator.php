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

use Fidry\Console\IO;
use Fidry\FileSystem\FileSystem;
use KevinGH\Box\NotInstantiable;
use const PHP_EOL;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use function trim;

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
