<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Differ;

use Infection\Differ\Differ;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ as BaseDiffer;

class DifferTest extends TestCase
{
    public function test_show_diffs_with_max_lines()
    {
        $source1 = <<<'CODE'
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
CODE;

        $source2 = <<<'CODE'
public function diff($from, $to, LongestCommonSubsequence $lcs = null)
{
    $diff = parent::diff($from, $to, $lcs);;
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
CODE;

        $expectedDiff = <<<'CODE'
--- Original
+++ New
@@ @@
 public function diff($from, $to, LongestCommonSubsequence $lcs = null)
 {
-    $diff = parent::diff($from, $to, $lcs);
+    $diff = parent::diff($from, $to, $lcs);;
     $characterCount = strlen($diff);
     $lineCount = 0;
     $characterIndex = 0;
     for ($characterIndex; $characterIndex < $characterCount; ++$characterIndex) {
         if ($diff[$characterIndex] === "\n") {
CODE;

        $differ = new Differ(
            new BaseDiffer()
        );

        $diff = $differ->diff($source1, $source2);

        if (\substr_count($diff, "\n") < Differ::DIFF_MAX_LINES - 1) {
            $this->markTestSkipped('See https://github.com/sebastianbergmann/diff/pull/59');
        }

        $this->assertSame($expectedDiff, $diff);
    }
}
