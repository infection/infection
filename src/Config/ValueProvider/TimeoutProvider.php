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

namespace Infection\Config\ValueProvider;

use Closure;
use Infection\Config\ConsoleHelper;
use Infection\Console\IO;
use Symfony\Component\Console\Exception\RuntimeException as SymfonyRuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class TimeoutProvider
{
    public const int DEFAULT_TIMEOUT = 10;

    private const array TIMEOUT_NOTICE = [
        '',
        'Infection limits how long each mutant test process is allowed to run.',
        'Any mutant process that exceeds this timeout will be killed and considered timed out.',
        'Furthermore, mutations that are known to take longer than this timeout will be skipped automatically.',
        '',
    ];

    public function __construct(
        private ConsoleHelper $consoleHelper,
        private QuestionHelper $questionHelper,
    ) {
    }

    /**
     * @throws SymfonyRuntimeException
     */
    public function get(IO $io): int|float
    {
        $io->writeln(self::TIMEOUT_NOTICE);

        $questionText = $this->consoleHelper->getQuestion(
            'What is the maximum allowed time in seconds for each mutant process?',
            (string) self::DEFAULT_TIMEOUT,
        );

        $question = new Question($questionText, self::DEFAULT_TIMEOUT);
        $question->setValidator($this->getValidator());

        /** @var int|float $answer */
        $answer = $this->questionHelper->ask(
            $io->getInput(),
            $io->getOutput(),
            $question,
        );

        return $answer;
    }

    /**
     * @return Closure(mixed): (int|float)
     */
    private function getValidator(): Closure
    {
        return static function (mixed $value): int|float {
            if ($value === '' || $value === null) {
                return self::DEFAULT_TIMEOUT;
            }

            Assert::numeric($value, 'The timeout must be a positive number.');

            $floatValue = (float) $value;

            Assert::greaterThan($floatValue, 0, 'The timeout must be a positive number.');

            return (float) (int) $floatValue === $floatValue ? (int) $floatValue : $floatValue;
        };
    }
}
