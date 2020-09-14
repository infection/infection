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

namespace Infection\Console;

use function array_key_exists;
use function in_array;
use Infection\CannotBeInstantiated;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
final class LogVerbosity
{
    use CannotBeInstantiated;

    public const DEBUG = 'all';
    public const NORMAL = 'default';
    public const NONE = 'none';

    /**
     * @deprecated
     */
    public const DEBUG_INTEGER = 1;

    /**
     * @deprecated
     */
    public const NORMAL_INTEGER = 2;

    /**
     * @deprecated
     */
    public const NONE_INTEGER = 3;

    /**
     * @var array<int, string>
     */
    public const ALLOWED_OPTIONS = [
        self::DEBUG_INTEGER => self::DEBUG,
        self::NORMAL_INTEGER => self::NORMAL,
        self::NONE_INTEGER => self::NONE,
    ];

    public static function convertVerbosityLevel(InputInterface $input, ConsoleOutput $io): void
    {
        $verbosityLevel = $input->getOption('log-verbosity');

        if (in_array($verbosityLevel, self::ALLOWED_OPTIONS, true)) {
            return;
        }

        // If that's non-standard, think it's a legacy numeric option.
        $verbosityLevel = (int) $verbosityLevel;

        if (array_key_exists($verbosityLevel, self::ALLOWED_OPTIONS)) {
            $input->setOption('log-verbosity', self::ALLOWED_OPTIONS[$verbosityLevel]);
            $io->logVerbosityDeprecationNotice(self::ALLOWED_OPTIONS[$verbosityLevel]);

            return;
        }

        $io->logUnknownVerbosityOption(self::NORMAL);
        $input->setOption('log-verbosity', self::NORMAL);
    }
}
