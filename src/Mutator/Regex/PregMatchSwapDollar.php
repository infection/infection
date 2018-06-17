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
final class PregMatchSwapDollar extends AbstractPregMatch
{
    protected function manipulatePattern(string $pattern): string
    {
        preg_match('/^([\/#~+])([^$]*)([$]?)\1([gmixXsuUAJD]*)$/', $pattern, $matches);
        $delimiter = $matches[1];
        $regexBody = $matches[2];
        $wasEndingSign = !empty($matches[3]);
        $flags = $matches[4];

        return $delimiter . $regexBody . ($wasEndingSign ? '' : '$') . $delimiter . $flags;
    }
}
