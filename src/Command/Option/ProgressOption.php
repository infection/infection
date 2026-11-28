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
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ProgressOption implements CommandOption
{
    use CannotBeInstantiated;

    public const string NO_PROGRESS = 'no-progress';

    public const string FORCE_PROGRESS = 'force-progress';

    /**
     * @template T of Command
     * @param T $command
     *
     * @return T
     */
    public static function addOption(Command $command): Command
    {
        $command
            ->addOption(
                self::NO_PROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Do not output progress bars and mutation count during progress. Automatically enabled if a CI is detected',
            )
            ->addOption(
                self::FORCE_PROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Output progress bars and mutation count during progress even if a CI is detected',
            )
        ;

        return $command;
    }

    /**
     * Null leaves the decision to CI detection.
     */
    public static function get(IO $io): ?bool
    {
        /** @var bool $noProgress */
        $noProgress = $io->getInput()->getOption(self::NO_PROGRESS);
        /** @var bool $forceProgress */
        $forceProgress = $io->getInput()->getOption(self::FORCE_PROGRESS);

        Assert::false(
            $noProgress && $forceProgress,
            sprintf(
                'Cannot pass both "%s" and "%s" option: use none or only one of them',
                self::NO_PROGRESS,
                self::FORCE_PROGRESS,
            ),
        );

        if ($noProgress) {
            return true;
        }

        if ($forceProgress) {
            return false;
        }

        return null;
    }
}
