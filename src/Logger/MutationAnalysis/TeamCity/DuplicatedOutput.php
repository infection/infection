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

namespace Infection\Logger\MutationAnalysis\TeamCity;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class DuplicatedOutput implements OutputInterface
{
    public function __construct(
        private OutputInterface $source,
        private OutputInterface $duplicate,
    ) {
    }

    public function write(iterable|string $messages, bool $newline = false, int $options = 0): void
    {
        $this->source->write($messages, $newline, $options);
        $this->duplicate->write($messages, $newline, $options);
    }

    public function writeln(iterable|string $messages, int $options = 0): void
    {
        $this->source->writeln($messages, $options);
        $this->duplicate->writeln($messages, $options);
    }

    public function setVerbosity(int $level): void
    {
        $this->source->setVerbosity($level);
        $this->duplicate->setVerbosity($level);
    }

    public function getVerbosity(): int
    {
        return $this->source->getVerbosity();
    }

    public function isQuiet(): bool
    {
        return $this->source->isQuiet();
    }

    public function isVerbose(): bool
    {
        return $this->source->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->source->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->source->isDebug();
    }

    public function setDecorated(bool $decorated): void
    {
        $this->source->setDecorated($decorated);
        $this->duplicate->setDecorated($decorated);
    }

    public function isDecorated(): bool
    {
        return $this->source->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->source->setFormatter($formatter);
        $this->duplicate->setFormatter($formatter);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->source->getFormatter();
    }
}
