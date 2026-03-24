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

namespace Infection\Tests\Source\Matcher;

use Infection\Differ\ChangedLinesRange;
use Infection\Source\Matcher\SimpleSourceLineMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(SimpleSourceLineMatcher::class)]
final class SimpleSourceLineMatcherTest extends TestCase
{
    /**
     * @param list<ChangedLinesRange> $changedLinesRanges
     * @param positive-int $mutationStartLine
     * @param positive-int $mutationEndLine
     */
    #[DataProvider('provideLines')]
    public function test_it_tells_if_the_mutation_touches_any_of_the_changed_lines(
        array $changedLinesRanges,
        int $mutationStartLine,
        int $mutationEndLine,
        bool $expected,
    ): void {
        $matcher = new SimpleSourceLineMatcher($changedLinesRanges);

        $actual = $matcher->touches(
            '/path/to/File.php',
            $mutationStartLine,
            $mutationEndLine,
        );

        $this->assertSame(
            $expected,
            $actual,
            sprintf('Line %d was not found in diff', $mutationStartLine),
        );
    }

    public static function provideLines(): iterable
    {
        yield 'the mutation touches no changed line' => [
            [ChangedLinesRange::forLine(3)],
            1,
            1,
            false,
        ];

        yield 'the mutation touches a changed line' => [
            [ChangedLinesRange::forLine(3)],
            2,
            5,
            true,
        ];

        yield 'the mutation touches none of the changed lines' => [
            [
                ChangedLinesRange::forLine(10),
                ChangedLinesRange::create(30, 50),
            ],
            12,
            15,
            false,
        ];

        yield 'the mutation touches one of the changed lines' => [
            [
                ChangedLinesRange::forLine(10),
                ChangedLinesRange::create(30, 50),
            ],
            4,
            12,
            true,
        ];
    }
}
