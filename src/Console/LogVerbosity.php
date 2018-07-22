<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console;

use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
final class LogVerbosity
{
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

    public const ALLOWED_OPTIONS = [
        self::DEBUG_INTEGER => self::DEBUG,
        self::NORMAL_INTEGER => self::NORMAL,
        self::NONE_INTEGER => self::NONE,
    ];

    public static function convertVerbosityLevel(InputInterface $input, ConsoleOutput $io): void
    {
        $verbosityLevel = $input->getOption('log-verbosity');

        if (\in_array($verbosityLevel, self::ALLOWED_OPTIONS)) {
            return;
        }

        if (array_key_exists((int) $verbosityLevel, self::ALLOWED_OPTIONS)) {
            $input->setOption('log-verbosity', self::ALLOWED_OPTIONS[$verbosityLevel]);
            $io->logVerbosityDeprecationNotice(self::ALLOWED_OPTIONS[$verbosityLevel]);

            return;
        }

        $io->logUnkownVerbosityOption(self::NORMAL);
        $input->setOption('log-verbosity', self::NORMAL);
    }
}
