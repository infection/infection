<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console;

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

    /**
     * @param int|string $verbosityLevel
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function convertVerbosityLevel($verbosityLevel): string
    {
        if (in_array($verbosityLevel, self::ALLOWED_OPTIONS)) {
            return $verbosityLevel;
        }

        if (array_key_exists((int) $verbosityLevel, self::ALLOWED_OPTIONS)) {
            return self::ALLOWED_OPTIONS[$verbosityLevel];
        }

        throw new \Exception();
    }
}
