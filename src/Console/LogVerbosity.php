<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class LogVerbosity
{
    const DEBUG = 'all';
    const NORMAL = 'default';
    const NONE = 'none';

    /**
     * @deprecated
     */
    const DEBUG_INTEGER = 1;

    /**
     * @deprecated
     */
    const NORMAL_INTEGER = 2;

    /**
     * @deprecated
     */
    const NONE_INTEGER = 3;

    const ALLOWED_OPTIONS = [
        self::DEBUG_INTEGER => self::DEBUG,
        self::NORMAL_INTEGER => self::NORMAL,
        self::NONE_INTEGER => self::NONE,
    ];

    public static function convertVerbosityLevel(InputInterface $input, SymfonyStyle $io)
    {
        $verbosityLevel = $input->getOption('log-verbosity');
        if (in_array($verbosityLevel, self::ALLOWED_OPTIONS)) {
            return;
        }

        if (array_key_exists((int) $verbosityLevel, self::ALLOWED_OPTIONS)) {
            $input->setOption('log-verbosity', self::ALLOWED_OPTIONS[$verbosityLevel]);
            $io->note('Numeric versions of log-verbosity have been deprecated, please use, ' . self::ALLOWED_OPTIONS[$verbosityLevel] . ' to keep the same result');

            return;
        }

        $io->note('Running infection with an unknown log-verbosity option, falling back to \'default\' option');
        $input->setOption('log-verbosity', self::NORMAL);
    }
}
