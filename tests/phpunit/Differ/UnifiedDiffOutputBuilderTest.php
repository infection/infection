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

use Infection\Differ\UnifiedDiffOutputBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;

#[CoversClass(UnifiedDiffOutputBuilder::class)]
#[Group('integration')]
final class UnifiedDiffOutputBuilderTest extends TestCase
{
    public function test_it_returns_an_empty_diff_when_there_is_no_header_or_diff(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame('', $builder->getDiff([]));
    }

    public function test_it_keeps_the_configured_header_for_empty_diffs(): void
    {
        $builder = new UnifiedDiffOutputBuilder("--- Original\n+++ New\n");

        $this->assertSame(
            "--- Original\n+++ New\n",
            $builder->getDiff([]),
        );
    }

    public function test_it_builds_a_unified_diff_without_line_numbers(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            <<<'DIFF'
                @@ @@
                 line 1
                -line 2
                +changed
                 line 3

                DIFF,
            $builder->getDiff([
                ["line 1\n", Differ::OLD],
                ["line 2\n", Differ::REMOVED],
                ["changed\n", Differ::ADDED],
                ["line 3\n", Differ::OLD],
            ]),
        );
    }

    public function test_it_can_build_a_unified_diff_with_line_numbers(): void
    {
        $builder = new UnifiedDiffOutputBuilder('', true);

        $this->assertSame(
            <<<'DIFF'
                @@ -1,3 +1,3 @@
                 line 1
                -line 2
                +changed
                 line 3

                DIFF,
            $builder->getDiff([
                ["line 1\n", Differ::OLD],
                ["line 2\n", Differ::REMOVED],
                ["changed\n", Differ::ADDED],
                ["line 3\n", Differ::OLD],
            ]),
        );
    }

    public function test_it_adds_missing_trailing_line_breaks_to_headers(): void
    {
        $builder = new UnifiedDiffOutputBuilder('Header');

        $this->assertSame(
            <<<'DIFF'
                Header
                @@ @@
                -old
                +new

                DIFF,
            $builder->getDiff([
                ["old\n", Differ::REMOVED],
                ["new\n", Differ::ADDED],
            ]),
        );
    }

    public function test_it_adds_a_trailing_line_break_when_the_diff_does_not_have_one(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            <<<'DIFF'
                @@ @@
                -old
                +new

                DIFF,
            $builder->getDiff([
                ['old', Differ::REMOVED],
                ['new', Differ::ADDED],
            ]),
        );
    }

    public function test_it_adds_missing_trailing_line_breaks_to_carriage_return_headers(): void
    {
        $builder = new UnifiedDiffOutputBuilder("Header\r");

        $this->assertSame("Header\r\n", $builder->getDiff([]));
    }

    public function test_it_preserves_warning_tokens_as_blank_lines(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            <<<'DIFF'
                @@ @@
                 line

                +changed

                DIFF,
            $builder->getDiff([
                ["line\n", Differ::OLD],
                ["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING],
                ["changed\n", Differ::ADDED],
            ]),
        );
    }

    public function test_it_ignores_no_line_end_warnings_when_there_is_no_change(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            '',
            $builder->getDiff([
                ["before\n", Differ::OLD],
                ["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING],
                ["after\n", Differ::OLD],
            ]),
        );
    }

    public function test_it_preserves_carriage_return_terminated_line_ending_warnings(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            "@@ @@\n warning\r",
            $builder->getDiff([
                ["warning\r", Differ::DIFF_LINE_END_WARNING],
            ]),
        );
    }

    public function test_it_adds_missing_line_breaks_for_changed_lines_at_the_end_of_a_file(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            <<<'DIFF'
                @@ @@
                -old+new context

                DIFF,
            $builder->getDiff([
                ['old', Differ::REMOVED],
                ['new', Differ::ADDED],
                ['context', Differ::OLD],
            ]),
        );
    }

    public function test_it_adds_missing_line_breaks_for_added_lines_before_removed_lines(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            <<<'DIFF'
                @@ @@
                +new
                -old

                DIFF,
            $builder->getDiff([
                ['new', Differ::ADDED],
                ["old\n", Differ::REMOVED],
            ]),
        );
    }

    public function test_it_only_checks_the_latest_added_and_removed_lines_for_missing_line_breaks(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            <<<'DIFF'
                @@ @@
                +earlier context
                +new
                -old

                DIFF,
            $builder->getDiff([
                ['earlier', Differ::ADDED],
                ["context\n", Differ::OLD],
                ['new', Differ::ADDED],
                ["old\n", Differ::REMOVED],
            ]),
        );
    }

    public function test_it_splits_distant_changes_into_separate_hunks(): void
    {
        $builder = new UnifiedDiffOutputBuilder('');

        $this->assertSame(
            <<<'DIFF'
                @@ @@
                 1
                 2
                +A
                 3
                 4
                 5
                @@ @@
                 10
                 11
                 12
                +B
                 13
                 14
                 15

                DIFF,
            $builder->getDiff(self::createTwoDistantChangesDiff()),
        );
    }

    public function test_it_calculates_line_numbers_for_separate_hunks(): void
    {
        $builder = new UnifiedDiffOutputBuilder('', true);

        $this->assertSame(
            <<<'DIFF'
                @@ -1,5 +1,6 @@
                 1
                 2
                +A
                 3
                 4
                 5
                @@ -10,6 +11,7 @@
                 10
                 11
                 12
                +B
                 13
                 14
                 15

                DIFF,
            $builder->getDiff(self::createTwoDistantChangesDiff()),
        );
    }

    public function test_it_collapses_single_line_ranges(): void
    {
        $builder = new UnifiedDiffOutputBuilder('', true);

        $this->assertSame(
            <<<'DIFF'
                @@ -1 +1 @@
                -old
                +new

                DIFF,
            $builder->getDiff([
                ["old\n", Differ::REMOVED],
                ["new\n", Differ::ADDED],
            ]),
        );
    }

    public function test_it_does_not_collapse_multi_line_ranges(): void
    {
        $builder = new UnifiedDiffOutputBuilder('', true);

        $this->assertSame(
            <<<'DIFF'
                @@ -1,2 +1 @@
                -old1
                -old2
                +new

                DIFF,
            $builder->getDiff([
                ["old1\n", Differ::REMOVED],
                ["old2\n", Differ::REMOVED],
                ["new\n", Differ::ADDED],
            ]),
        );

        $this->assertSame(
            <<<'DIFF'
                @@ -1 +1,2 @@
                -old
                +new1
                +new2

                DIFF,
            $builder->getDiff([
                ["old\n", Differ::REMOVED],
                ["new1\n", Differ::ADDED],
                ["new2\n", Differ::ADDED],
            ]),
        );
    }

    /**
     * @return list<array{string, int}>
     */
    private static function createTwoDistantChangesDiff(): array
    {
        $diff = [];

        for ($i = 1; $i <= 20; ++$i) {
            $diff[] = ["$i\n", Differ::OLD];

            if ($i === 2) {
                $diff[] = ["A\n", Differ::ADDED];
            }

            if ($i === 12) {
                $diff[] = ["B\n", Differ::ADDED];
            }
        }

        return $diff;
    }
}
