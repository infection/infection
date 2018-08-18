<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Regex;

/**
 * @internal
 */
final class PregMatchRemoveDollar extends AbstractPregMatch
{
    public const ANALYSE_REGEX = '/^([\/#~+])([^$]*)([$]?)\1([gmixXsuUAJD]*)$/';

    protected function manipulatePattern(string $pattern): string
    {
        preg_match(self::ANALYSE_REGEX, $pattern, $matches);
        $delimiter = $matches[1] ?? '';
        $regexBody = $matches[2] ?? '';
        $flags = $matches[4] ?? '';

        return $delimiter . $regexBody . $delimiter . $flags;
    }

    protected function isProperRegexToMutate(string $pattern): bool
    {
        preg_match(self::ANALYSE_REGEX, $pattern, $matches);

        return !empty($matches[3]);
    }
}
