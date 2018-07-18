<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework;

/**
 * @internal
 */
final class TestFrameworkTypes
{
    public const PHPUNIT = 'phpunit';
    public const PHPSPEC = 'phpspec';

    public const TYPES = [
        self::PHPUNIT,
        self::PHPSPEC,
    ];
}
