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

use function array_map;
use function explode;
use function implode;
use Infection\Differ\Differ;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

#[CoversClass(Differ::class)]
final class DifferTest extends TestCase
{
    #[DataProvider('diffProvider')]
    public function test_it_shows_the_diff_between_two_sources_but_limiting_the_displayed_lines(
        string $sourceA,
        string $sourceB,
        string $expectedDiff,
    ): void {
        $actualDiff = (new Differ(new BaseDiffer(new UnifiedDiffOutputBuilder())))->diff($sourceA, $sourceB);

        $this->assertSame($expectedDiff, self::normalizeString($actualDiff));
    }

    public static function diffProvider(): iterable
    {
        yield 'empty' => [
            '',
            '',
            <<<'PHP'
                --- Original
                +++ New

                PHP,
        ];

        yield 'nominal' => [
            <<<'PHP'

                public function echo(): void
                {
                    echo 10;
                }

                PHP
            ,
            <<<'PHP'

                public function echo(): void
                {
                    echo 15;
                }

                PHP
            ,
            <<<'PHP'
                --- Original
                +++ New
                @@ @@

                 public function echo(): void
                 {
                -    echo 10;
                +    echo 15;
                 }

                PHP,
        ];

        yield 'no change' => [
            <<<'PHP'

                public function echo(): void
                {
                    echo 10;
                }

                PHP
            ,
            <<<'PHP'

                public function echo(): void
                {
                    echo 10;
                }

                PHP
            ,
            <<<'PHP'
                --- Original
                +++ New

                PHP,
        ];

        yield 'line excess' => [
            <<<'PHP'
                0
                1
                2
                3
                4
                5
                6
                7
                8
                9
                10
                11
                12
                13
                14
                15
                PHP
            ,
            <<<'PHP'
                0
                1
                2
                3
                4
                5
                (6)
                7
                8
                9
                10
                11
                12
                13
                14
                15
                PHP
            ,
            <<<'PHP'
                --- Original
                +++ New
                @@ @@
                 3
                 4
                 5
                -6
                +(6)
                 7
                 8
                 9

                PHP,
        ];
    }

    private static function normalizeString(string $string): string
    {
        return implode(
            "\n",
            array_map('rtrim', explode("\n", $string)),
        );
    }
}
