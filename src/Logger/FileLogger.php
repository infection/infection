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

namespace Infection\Logger;

use function implode;
use function in_array;
use const PHP_EOL;
use function Safe\file_put_contents;
use function Safe\sprintf;
use function strpos;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class FileLogger implements MutationTestingResultsLogger
{
    private $filePath;
    private $fileSystem;
    private $output;
    private $lineLogger;

    public function __construct(
        OutputInterface $output,
        string $filePath,
        Filesystem $fileSystem,
        LineMutationTestingResultsLogger $lineLogger
    ) {
        $this->filePath = $filePath;
        $this->fileSystem = $fileSystem;
        $this->output = $output;
        $this->lineLogger = $lineLogger;
    }

    public function log(): void
    {
        $content = implode(PHP_EOL, $this->lineLogger->getLogLines());

        // If the output should be written to a stream then just write it directly
        if (strpos($this->filePath, 'php://') === 0) {
            if (in_array($this->filePath, ['php://stdout', 'php://stderr'], true)) {
                file_put_contents($this->filePath, $content);
            } else {
                // The Symfony filesystem component doesn't support using streams so provide a
                // sensible error message
                $this->output->writeln(sprintf(
                    '<error>%s</error>',
                    'The only streams supported are php://stdout and php://stderr'
                ));
            }

            return;
        }

        try {
            $this->fileSystem->dumpFile($this->filePath, $content);
        } catch (IOException $exception) {
            $this->output->writeln(sprintf(
                '<error>%s</error>',
                $exception->getMessage()
            ));
        }
    }
}
