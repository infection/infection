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

namespace Infection\Tests\Source\Exception;

use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Source\Exception\NoSourceFound;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoSourceFound::class)]
final class NoSourceFoundTest extends TestCase
{
    public function test_it_can_be_created_for_when_no_source_file_was_found_for_a_git_diff(): void
    {
        $expected = new NoSourceFound(
            isSourceFiltered: true,
            message: 'Could not find any modified files in the configured sources for the git filter "AM" and the base "5bb63416f37ab06705b3ff2decdc96051b2989de".',
        );

        $actual = NoSourceFound::noFilesForGitDiff('AM', '5bb63416f37ab06705b3ff2decdc96051b2989de');

        $this->assertEquals($expected, $actual);
    }

    #[DataProvider('changedLinesDiffProvider')]
    public function test_it_can_be_created_for_when_no_changed_lines_was_found_for_a_git_diff(
        string $diffFilter,
        string $base,
        string $diff,
        string $expected,
    ): void {
        $expected = new NoSourceFound(
            isSourceFiltered: true,
            message: $expected,
        );

        $actual = NoSourceFound::noChangedLinesForGitDiff($diffFilter, $base, $diff);

        $this->assertEquals($expected, $actual);
    }

    public static function changedLinesDiffProvider(): iterable
    {
        yield 'empty diff' => [
            'AM',
            '5bb63416f37ab06705b3ff2decdc96051b2989de',
            '',
            'Could not find any modified lines for the git filter "AM" and the base "5bb63416f37ab06705b3ff2decdc96051b2989de". The diff got was blank.',
        ];

        yield 'blank diff' => [
            'AM',
            '5bb63416f37ab06705b3ff2decdc96051b2989de',
            " \n \n\r ",
            'Could not find any modified lines for the git filter "AM" and the base "5bb63416f37ab06705b3ff2decdc96051b2989de". The diff got was blank.',
        ];

        yield 'non-blank diff' => [
            'AM',
            '5bb63416f37ab06705b3ff2decdc96051b2989de',
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,5 +12,7 @@ line change example
                DIFF,
            <<<'EOF'
                Could not find any modified lines for the git filter "AM" and the base "5bb63416f37ab06705b3ff2decdc96051b2989de". The diff got was:

                """
                diff --git a/src/Container.php b/src/Container.php
                index 2a9e281..01cbf04 100644
                --- a/src/Container.php
                +++ b/src/Container.php
                @@ -10,5 +12,7 @@ line change example
                """
                EOF,
        ];
    }

    #[DataProvider('plainFilterProvider')]
    public function test_it_can_be_created_for_when_no_source_file_was_found(
        ?PlainFilter $filter,
        bool $expectedSourceFiltered,
        string $expectedMessage,
    ): void {
        $expected = new NoSourceFound(
            isSourceFiltered: $expectedSourceFiltered,
            message: $expectedMessage,
        );

        $actual = NoSourceFound::noSourceFileFound($filter);

        $this->assertEquals($expected, $actual);
    }

    public static function plainFilterProvider(): iterable
    {
        yield 'no filter applied' => [
            null,
            false,
            'No source file found for the configured sources.',
        ];

        yield 'filter applied' => [
            new PlainFilter([
                'src/File1.php',
                'src/File2.php',
            ]),
            true,
            'No source file found for the filter applied to the configured sources. The filter used was: "src/File1.php,src/File2.php".',
        ];
    }
}
