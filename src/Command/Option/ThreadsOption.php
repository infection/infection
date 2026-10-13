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

namespace Infection\Command\Option;

use Infection\CannotBeInstantiated;
use Infection\Console\IO;
use Infection\Container\Container;
use function is_numeric;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ThreadsOption implements CommandOption
{
    use CannotBeInstantiated;

    public const string NAME = 'threads';

    /**
     * @template T of Command
     * @param T $command
     *
     * @return T
     */
    public static function addOption(Command $command): Command
    {
        $command->addOption(
            self::NAME,
            'j',
            InputOption::VALUE_REQUIRED,
            'Number of threads to use by the runner when executing the mutations. Use "max" to auto calculate it.',
            Container::DEFAULT_THREAD_COUNT,
        );

        return $command;
    }

    /**
     * @return int|'max'|null
     */
    public static function get(IO $io): int|string|null
    {
        /** @var string|null $threads */
        $threads = $io->getInput()->getOption(self::NAME);

        // user didn't pass `--threads` option
        if ($threads === null) {
            return null;
        }

        // user passed `--threads=<int>` option
        if (is_numeric($threads)) {
            $threadCount = (int) $threads;

            Assert::positiveInteger(
                $threadCount,
                sprintf(
                    'The value of option `--threads` must be a positive integer. String "%s" provided.',
                    $threads,
                ),
            );

            return $threadCount;
        }

        // user passed `--threads=max` option
        Assert::same(
            $threads,
            'max',
            sprintf(
                'The value of option `--threads` must be of type integer or string "max". String "%s" provided.',
                $threads,
            ),
        );

        return 'max';
    }
}
