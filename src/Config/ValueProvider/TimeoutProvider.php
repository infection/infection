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

use Infection\Config\ConsoleHelper;
use Infection\Configuration\ConfigurationFactory;
use Infection\Console\IO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Exception\RuntimeException as SymfonyRuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class TimeoutProvider
{
    private const int DEFAULT_TIMEOUT = ConfigurationFactory::DEFAULT_TIMEOUT;

    private const array TIMEOUT_EXPLANATION = [
        '',
        'Infection limits how long the tests may run for each mutant.',
        'If this limit is exceeded, Infection stops the process and marks the mutant as timed out.',
        'Allow enough time for the tests to finish normally to avoid misleading mutation scores.',
        '',
    ];

    public function __construct(
        private ConsoleHelper $consoleHelper,
        private QuestionHelper $questionHelper,
        private LoggerInterface $logger,
    ) {
    }

    public function get(IO $io): float
    {
        $io->writeln(self::TIMEOUT_EXPLANATION);

        $questionText = $this->consoleHelper->getQuestion(
            'What is the maximum allowed time in seconds for each mutant process?',
            (string) self::DEFAULT_TIMEOUT,
        );

        $question = new Question($questionText, (float) self::DEFAULT_TIMEOUT);
        $question->setValidator($this->validate(...));

        try {
            /** @var float $answer */
            $answer = $this->questionHelper->ask(
                $io->getInput(),
                $io->getOutput(),
                $question,
            );

            Assert::float($answer, 'Expected timeout to be a float.');

            return $answer;
        } catch (SymfonyRuntimeException $exception) {
            $this->logger->debug('Failed to get timeout, falling back to default.', ['exception' => $exception]);

            return (float) self::DEFAULT_TIMEOUT;
        }
    }

    private function validate(mixed $value): float
    {
        Assert::numeric($value, 'The timeout must be a positive number.');

        $timeout = (float) $value;

        Assert::greaterThan($timeout, 0, 'The timeout must be a positive number.');

        return $timeout;
    }
}
