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

use function in_array;
use Infection\CannotBeInstantiated;
use Infection\Console\IO;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use InvalidArgumentException;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final class MapSourceClassToTestOption implements CommandOption
{
    use CannotBeInstantiated;

    public const NAME = 'map-source-class-to-test';

    /**
     * @template T of Command
     */
    public static function addOption(Command $command): Command
    {
        return $command->addOption(
            self::NAME,
            null,
            InputOption::VALUE_OPTIONAL,
            'Enables test files filtering during "Initial Tests Run" stage when `--filter`/`--git-diff-filter`/`--git-diff-lines` are used. With this option, only those test files are executed to provide coverage, that cover changed/added source files.',
            false,
        );
    }

    /**
     * @return MapSourceClassToTestStrategy::*|null
     */
    public static function get(IO $io): ?string
    {
        $inputValue = $io->getInput()->getOption(self::NAME);

        // `false` means the option was not provided at all -> user does not care and it will be auto-detected
        // `null` means the option was provided without any argument -> user wants to enable it
        // any string: the argument provided, but only `'simple'` is allowed for now
        if ($inputValue === false) {
            return null;
        }

        if ($inputValue === null) {
            return MapSourceClassToTestStrategy::SIMPLE;
        }

        self::assertIsValid($inputValue);

        return $inputValue;
    }

    /**
     * @phpstan-assert MapSourceClassToTestStrategy::* $inputValue
     */
    private static function assertIsValid(string $inputValue): void
    {
        if (in_array($inputValue, MapSourceClassToTestStrategy::getAll(), true)) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Cannot pass "%s" to "--%s": only "%s" or no argument is supported',
                $inputValue,
                self::NAME,
                MapSourceClassToTestStrategy::SIMPLE,
            ),
        );
    }
}
