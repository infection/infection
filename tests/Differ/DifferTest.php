<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Differ;

use Infection\Differ\Differ;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ as BaseDiffer;

/**
 * @internal
 */
final class DifferTest extends TestCase
{
    public function test_show_diffs_with_max_lines(): void
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

        if (substr_count($diff, "\n") < Differ::DIFF_MAX_LINES - 1) {
            $this->markTestSkipped('See https://github.com/sebastianbergmann/diff/pull/59');
        }

        $this->assertSame($expectedDiff, $diff);
    }
}
