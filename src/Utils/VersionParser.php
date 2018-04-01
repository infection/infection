<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Utils;

/**
 * @internal
 */
class VersionParser
{
    const VERSION_REGEX = '/(?<version>[0-9]+\.[0-9]+\.?[0-9]*)(?<prerelease>-[0-9a-zA-Z.]+)?(?<build>\+[0-9a-zA-Z.]+)?/';

    public function parse(string $content): string
    {
        $matches = [];
        $matched = preg_match(self::VERSION_REGEX, $content, $matches);

        if (!$matched) {
            throw new \InvalidArgumentException('Parameter does not contain a valid SemVer (sub)string.');
        }

        return $matches[0];
    }
}
