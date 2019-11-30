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

namespace Infection\Console\OutputFormatter;

use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;
use function strlen;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class DotFormatter extends AbstractOutputFormatter
{
    private const DOTS_PER_ROW = 50;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function start(int $mutationCount): void
    {
        parent::start($mutationCount);

        $this->output->writeln([
            '',
            '<killed>.</killed>: killed, '
            . '<escaped>M</escaped>: escaped, '
            . '<uncovered>S</uncovered>: uncovered, '
            . '<with-error>E</with-error>: fatal error, '
            . '<timeout>T</timeout>: timed out',
            '',
        ]);
    }

    public function advance(MutantProcessInterface $mutantProcess, int $mutationCount): void
    {
        parent::advance($mutantProcess, $mutationCount);

        switch ($mutantProcess->getResultCode()) {
            case MutantProcess::CODE_KILLED:
                $this->output->write('<killed>.</killed>');

                break;
            case MutantProcess::CODE_NOT_COVERED:
                $this->output->write('<uncovered>S</uncovered>');

                break;
            case MutantProcess::CODE_ESCAPED:
                $this->output->write('<escaped>M</escaped>');

                break;
            case MutantProcess::CODE_TIMED_OUT:
                $this->output->write('<timeout>T</timeout>');

                break;
            case MutantProcess::CODE_ERROR:
                $this->output->write('<with-error>E</with-error>');

                break;
        }

        $remainder = $this->callsCount % self::DOTS_PER_ROW;
        $endOfRow = 0 === $remainder;
        $lastDot = $mutationCount === $this->callsCount;

        if ($lastDot && !$endOfRow) {
            $this->output->write(str_repeat(' ', self::DOTS_PER_ROW - $remainder));
        }

        if ($lastDot || $endOfRow) {
            $length = strlen((string) $mutationCount);
            $format = sprintf('   (%%%dd / %%%dd)', $length, $length);

            $this->output->write(sprintf($format, $this->callsCount, $mutationCount));

            if ($this->callsCount !== $mutationCount) {
                $this->output->writeln('');
            }
        }
    }
}
