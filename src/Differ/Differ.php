<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Differ;

use SebastianBergmann\Diff\Differ as BaseDiffer;

class Differ
{
    const DIFF_MAX_LINES = 12;

    /**
     * @var BaseDiffer
     */
    private $differ;

    /**
     * Differ constructor.
     *
     * @param BaseDiffer $differ
     */
    public function __construct(BaseDiffer $differ)
    {
        $this->differ = $differ;
    }

    /**
     * Returns the diff between two arrays or strings as string.
     *
     * Overridden to show just DIFF_MAX_LINES lines of the diff
     *
     * @param array|string $from
     * @param array|string $to
     *
     * @return string
     */
    public function diff($from, $to)
    {
        $diff = $this->differ->diff($from, $to);

        $characterCount = strlen($diff);
        $lineCount = 0;
        $characterIndex = 0;

        for (; $characterIndex < $characterCount; ++$characterIndex) {
            if ($diff[$characterIndex] === "\n") {
                ++$lineCount;
                if ($lineCount >= self::DIFF_MAX_LINES) {
                    break;
                }
            }
        }

        return substr($diff, 0, $characterIndex);
    }
}
