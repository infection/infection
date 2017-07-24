<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Differ;

use SebastianBergmann\Diff\Differ as BaseDiffer;
use SebastianBergmann\Diff\LCS\LongestCommonSubsequence;

class Differ extends BaseDiffer
{
    const DIFF_MAX_LINES = 12;

    /**
     * Overridden to show just DIFF_MAX_LINES lines of the diff
     *
     * {@inheritdoc}
     */
    public function diff($from, $to, LongestCommonSubsequence $lcs = null)
    {
        $diff = parent::diff($from, $to, $lcs);

        $characterCount = strlen($diff);
        $lineCount = 0;
        $characterIndex = 0;

        for ($characterIndex; $characterIndex < $characterCount; ++$characterIndex) {
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
