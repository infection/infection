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

use function array_filter;
use function array_map;
use function array_values;
use function explode;
use Infection\CannotBeInstantiated;
use Infection\Console\IO;
use function is_string;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use function trim;

/**
 * @internal
 */
final class PathsArgument
{
    use CannotBeInstantiated;

    public const string SLOT_1_NAME = 'path';

    public const string SLOT_2_NAME = 'secondary-path';

    /**
     * @template T of Command
     * @param T $command
     *
     * @return T
     */
    public static function addArgument(Command $command): Command
    {
        return $command
            ->addArgument(
                self::SLOT_1_NAME,
                InputArgument::OPTIONAL,
                'A source or test path to focus mutation testing on. Source paths can be comma-separated to mutate multiple files (e.g. "src/A.php,src/B.php") - same convention as --filter. Test paths must be a single file or directory. Source vs test is auto-detected against the configured "source.directories".',
            )
            ->addArgument(
                self::SLOT_2_NAME,
                InputArgument::OPTIONAL,
                'A source or test path of the opposite kind to the first argument. Same rules apply (commas allowed for source only). Argument order is interchangeable.',
            );
    }

    /**
     * @return list<non-empty-string>
     */
    public static function getSlot1(IO $io): array
    {
        return self::readSlot($io, self::SLOT_1_NAME);
    }

    /**
     * @return list<non-empty-string>
     */
    public static function getSlot2(IO $io): array
    {
        return self::readSlot($io, self::SLOT_2_NAME);
    }

    /**
     * @return list<non-empty-string>
     */
    private static function readSlot(IO $io, string $name): array
    {
        $raw = $io->getInput()->getArgument($name);

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        return array_values(
            array_filter(
                array_map(trim(...), explode(',', $raw)),
                static fn (string $path): bool => $path !== '',
            ),
        );
    }
}
