<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Differ;

/**
 * @internal
 */
class DiffColorizer
{
    public function colorize(string $diff): string
    {
        $lines = array_map(function (string $line) {
            if (0 === strpos($line, '-')) {
                return  sprintf('<diff-del>%s</diff-del>', $line);
            }

            if (0 === strpos($line, '+')) {
                return sprintf('<diff-add>%s</diff-add>', $line);
            }

            return $line;
        }, explode("\n", $diff));

        return sprintf('<code>%s%s</code>', "\n", implode("\n", $lines));
    }
}
