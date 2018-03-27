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
        self::DEBUG,
        self::NORMAL,
        self::NONE,
        self::DEBUG_INTEGER,
        self::NORMAL_INTEGER,
        self::NONE_INTEGER,
    ];
}
