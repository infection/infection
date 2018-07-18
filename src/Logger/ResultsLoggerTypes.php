<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

/**
 * @internal
 */
final class ResultsLoggerTypes
{
    public const TEXT_FILE = 'text';
    public const SUMMARY_FILE = 'summary';
    public const DEBUG_FILE = 'debug';
    public const BADGE = 'badge';
    public const PER_MUTATOR = 'perMutator';

    public const ALL = [
        self::TEXT_FILE,
        self::DEBUG_FILE,
        self::SUMMARY_FILE,
        self::BADGE,
        self::PER_MUTATOR,
    ];

    public const ALLOWED_WITHOUT_LOGGING = [
        self::BADGE,
    ];
}
