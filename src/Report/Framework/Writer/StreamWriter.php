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

namespace Infection\Report\Framework\Writer;

use function is_string;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

/**
 * Writes the content to a stream. It is implemented as a wrapper around the
 * Symfony ConsoleOutput – which is a streamable OutputInterface, for simplicity.
 *
 * @internal
 */
final readonly class StreamWriter implements ReportWriter
{
    public const string STDOUT_STREAM = 'php://stdout';

    public const string STDERR_STREAM = 'php://stderr';

    public function __construct(
        private OutputInterface $output,
    ) {
    }

    /**
     * @param self::STDOUT_STREAM|self::STDERR_STREAM $stream
     */
    public static function createForStream(string $stream): self
    {
        $output = match ($stream) {
            self::STDOUT_STREAM => new ConsoleOutput(),
            self::STDERR_STREAM => (new ConsoleOutput())->getErrorOutput(),
            default => throw new UnexpectedValueException(),
        };

        return new self($output);
    }

    public function write(iterable|string $contentOrLines): void
    {
        if (is_string($contentOrLines)) {
            $this->output->write($contentOrLines);
        } else {
            foreach ($contentOrLines as $line) {
                $this->output->writeln($line);
            }
        }
    }
}
