<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
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

use Generator;
use Infection\Differ\DiffSourceCodeMatcher;
use PHPUnit\Framework\TestCase;

final class DiffSourceCodeMatcherTest extends TestCase
{
    /** @var DiffSourceCodeMatcher */
    private $diffSourceCodeMatcher;

    protected function setUp(): void
    {
        $this->diffSourceCodeMatcher = new DiffSourceCodeMatcher();
    }

    /**
     * @dataProvider diffRegexProvider
     */
    public function test_it_matches_diff_with_provided_regex(string $regex, string $diff, bool $expectedMatches): void
    {
        $matches = $this->diffSourceCodeMatcher->matches($diff, $regex);

        $this->assertSame($expectedMatches, $matches, 'Provided regex does not match the Mutant\'s diff');
    }

    public function diffRegexProvider(): Generator
    {
        yield 'Method name with PublicVisibility mutator' => [
            '.*getString.*',
            <<<'DIFF'
--- Original
+++ New
@@ @@
         $this->getString();
         return 'hello';
     }
-    public function getString()
+    protected function getString()
     {
         return 'string';
     }
 }
DIFF
            ,
            true,
        ];

        yield 'Method name with MethodCallRemoval mutator' => [
            '.*getString.*',
            <<<'DIFF'
--- Original
+++ New
@@ @@
     public function hello() : string
     {
         Assert::numeric('1');
-        $this->getString();
+
         return 'hello';
     }
     public function getString()
DIFF
            ,
            true,
        ];

        yield 'Method name with not related PublicVisibility mutator' => [
            'getString',
            <<<'DIFF'
--- Original
+++ New
@@ @@
 use Webmozart\Assert\Assert;
 class SourceClass
 {
-    public function hello() : string
+    protected function hello() : string
     {
         Assert::numeric('1');
         $this->getString();
DIFF
            ,
            false,
        ];

        yield 'Method call on object with MethodCallRemoval mutator' => [
            '\$this->getString\(\);',
            <<<'DIFF'
--- Original
+++ New
@@ @@
     public function hello() : string
     {
         Assert::numeric('1');
-        $this->getString();
+
         return 'hello';
     }
     public function getString()
DIFF
            ,
            true,
        ];

        yield 'All methods of static class calls with MethodCallRemoval mutator' => [
            'Assert::.*',
            <<<'DIFF'
--- Original
+++ New
@@ @@
 {
     public function hello() : string
     {
-        Assert::numeric('1');
+
         $this->getString();
         return 'hello';
     }
DIFF
            ,
            true,
        ];

        yield 'Method name with the minus operator' => [
            '.*getString.*',
            <<<'DIFF'
--- Original
+++ New
@@ @@

+ $a - 2 + $this->getString();
DIFF
            ,
            false,
        ];

        yield 'Regex contains delimiters should not lead to syntax error' => [
            '/getString/',
            <<<'DIFF'
--- Original
+++ New
@@ @@

+ $a - 2 + $this->getString();
DIFF
            ,
            false,
        ];
    }
}
