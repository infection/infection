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

use Infection\Differ\DiffColorizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DiffColorizer::class)]
final class DiffColorizerTest extends TestCase
{
    /**
     * @param non-empty-string $originalDiff
     * @param non-empty-string $expected
     */
    #[DataProvider('provideDiffs')]
    public function test_id_adds_colours_to_a_given_diff(string $originalDiff, string $expected): void
    {
        $actual = (new DiffColorizer())->colorize($originalDiff);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, list<non-empty-string>>
     */
    public static function provideDiffs(): iterable
    {
        yield 'full-deletion' => [
            <<<'CODE'
                     function ($a) {
                -        exit();
                +
                     }
                CODE,
            <<<'CODE'
                <code>
                     function ($a) {
                <diff-del>-<diff-del-inline>        exit();</diff-del-inline></diff-del>
                <diff-add>+</diff-add>
                     }</code>
                CODE,
        ];

        yield 'full-addition' => [
            <<<'CODE'
                     function ($a) {
                -
                +        exit();
                     }
                CODE,
            <<<'CODE'
                <code>
                     function ($a) {
                <diff-del>-</diff-del>
                <diff-add>+<diff-add-inline>        exit();</diff-add-inline></diff-add>
                     }</code>
                CODE,
        ];

        yield 'partial-deletion' => [
            <<<'CODE'
                     function ($a) {
                -        return 'foo' . 'bar';
                +        return 'foo';
                     }
                CODE,
            <<<'CODE'
                <code>
                     function ($a) {
                <diff-del>-        return 'foo'<diff-del-inline> . 'bar'</diff-del-inline>;</diff-del>
                <diff-add>+        return 'foo';</diff-add>
                     }</code>
                CODE,
        ];

        yield 'partial-addition' => [
            <<<'CODE'
                     function ($a) {
                -        return 'foo';
                +        return 'foo' . 'bar';
                     }
                CODE,
            <<<'CODE'
                <code>
                     function ($a) {
                <diff-del>-        return 'foo';</diff-del>
                <diff-add>+        return 'foo'<diff-add-inline> . 'bar'</diff-add-inline>;</diff-add>
                     }</code>
                CODE,
        ];

        yield 'deletion-and-addition' => [
            <<<'CODE'
                     function ($a, $b) {
                -        return $a && $b;
                +        return $a || $b;
                     }
                CODE,
            <<<'CODE'
                <code>
                     function ($a, $b) {
                <diff-del>-        return $a <diff-del-inline>&&</diff-del-inline> $b;</diff-del>
                <diff-add>+        return $a <diff-add-inline>||</diff-add-inline> $b;</diff-add>
                     }</code>
                CODE,
        ];

        // https://github.com/infection/infection/issues/1999
        yield 'bug-1999' => [
            <<<'CODE'
                     protected function name()
                     {
                -        return strtolower(get_class($this));
                +        strtolower(get_class($this));
                +        return null;
                     }
                 }
                CODE,
            <<<'CODE'
                <code>
                     protected function name()
                     {
                <diff-del>-        return strtolower(get_class($this));</diff-del>
                <diff-add>+        strtolower(get_class($this));</diff-add>
                <diff-add>+        return null;</diff-add>
                     }
                 }</code>
                CODE,
        ];

        yield 'multiple-removed-lines' => [
            <<<'CODE'
                         try {
                             $response = new Response();
                         } catch (RateLimitExceededException) {
                             throw new TooManyRequestsHttpException();
                -        } finally {
                -            $limiter->reset();
                         }
                +        $limiter->reset();
                         return $response;
                CODE,
            <<<'CODE'
                <code>
                         try {
                             $response = new Response();
                         } catch (RateLimitExceededException) {
                             throw new TooManyRequestsHttpException();
                <diff-del>-        } finally {</diff-del>
                <diff-del>-            $limiter->reset();</diff-del>
                         }
                <diff-add>+        $limiter->reset();</diff-add>
                         return $response;</code>
                CODE,
        ];

        yield 'code string containing symfony style-tags' => [
            <<<'CODE'

                - $output->writeln('<options=bold>' . $x . '</options=bold> mutants were caught by Static Analysis');
                + $output->writeln('<options=bold>' . $x . '</options=bold> mutants were caught by Static Analysis.');
                CODE,
            <<<'CODE'
                <code>

                <diff-del>- $output->writeln('\<options=bold>' . $x . '\</options=bold> mutants were caught by Static Analysis');</diff-del>
                <diff-add>+ $output->writeln('\<options=bold>' . $x . '\</options=bold> mutants were caught by Static Analysis<diff-add-inline>.</diff-add-inline>');</diff-add></code>
                CODE,
        ];

        yield 'surrounding comment containing symfony style-tags' => [
            <<<'CODE'

                // symfony docs suggest to use <error>foo</error>
                -        $changedLinesRangeXX = 1;
                +        $changedLinesRangeXX = 2;
                CODE,
            <<<'CODE'
                <code>

                // symfony docs suggest to use \<error>foo\</error>
                <diff-del>-        $changedLinesRangeXX = <diff-del-inline>1</diff-del-inline>;</diff-del>
                <diff-add>+        $changedLinesRangeXX = <diff-add-inline>2</diff-add-inline>;</diff-add></code>
                CODE,
        ];

        yield 'multibyte characters full-deletion' => [
            <<<'CODE'
                     function ($a) {
                -        return 'déjà_vu';
                +
                     }
                CODE,
            <<<'CODE'
                <code>
                     function ($a) {
                <diff-del>-<diff-del-inline>        return 'déjà_vu</diff-del-inline>';</diff-del>
                <diff-add>+</diff-add>
                     }</code>
                CODE,
        ];

        yield 'multibyte characters partial-deletion' => [
            <<<'CODE'
                     function ($a) {
                -        return 'déjà_vu' . 'bar';
                +        return 'déjà_vu';
                     }
                CODE,
            <<<'CODE'
                <code>
                     function ($a) {
                <diff-del>-        return 'déjà_v<diff-del-inline>u' . 'ba</diff-del-inline>r';</diff-del>
                <diff-add>+        return 'déjà_vu';</diff-add>
                     }</code>
                CODE,
        ];
    }
}
