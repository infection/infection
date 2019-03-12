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

namespace Infection\Tests\Mutator\Extensions;

use Generator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class MBStringTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator(string $input, string $expected = null, array $settings = []): void
    {
        $this->doTest($input, $expected, $settings);
    }

    public function provideMutationCases(): Generator
    {
        yield 'It converts mb_strlen with leading slash' => [
            "<?php \mb_strlen('test');",
            "<?php\n\nstrlen('test');",
        ];

        yield 'It converts mb_strlen with leading slash in namespace' => [
            "<?php namespace Test; \mb_strlen('test');",
            "<?php\n\nnamespace Test;\n\nstrlen('test');",
        ];

        yield 'It does not convert standard functions like strpos' => [
            "<?php strpos('test');",
        ];

        yield 'It converts mb_strlen with encoding to strlen' => [
            "<?php mb_strlen('test', 'utf-8');",
            "<?php\n\nstrlen('test');",
            ['settings' => ['mb_strlen' => true]],
        ];

        yield 'It does not convert mb_strlen when disabled' => [
            "<?php mb_strlen('test');",
            null,
            ['settings' => ['mb_strlen' => false]],
        ];

        yield from $this->provideMutationCasesForChr();

        yield from $this->provideMutationCasesForEregMatch();

        yield from $this->provideMutationCasesForEregReplaceCallback();

        yield from $this->provideMutationCasesForEregReplace();

        yield from $this->provideMutationCasesForEreg();

        yield from $this->provideMutationCasesForEregiReplace();

        yield from $this->provideMutationCasesForEregi();

        yield from $this->provideMutationCasesForOrd();

        yield from $this->provideMutationCasesForParseStr();

        yield from $this->provideMutationCasesForSendMail();

        yield from $this->provideMutationCasesForSplit();

        yield from $this->provideMutationCasesForStrCut();

        yield from $this->provideMutationCasesForStrPos();

        yield from $this->provideMutationCasesForStrIPos();

        yield from $this->provideMutationCasesForStrIStr();

        yield from $this->provideMutationCasesForStrRiPos();

        yield from $this->provideMutationCasesForStrRPos();

        yield from $this->provideMutationCasesForStrStr();

        yield from $this->provideMutationCasesForStrToLower();

        yield from $this->provideMutationCasesForStrToUpper();

        yield from $this->provideMutationCasesForSubStrCount();

        yield from $this->provideMutationCasesForSubStr();

        yield from $this->provideMutationCasesForStrRChr();

        yield from $this->provideMutationCasesForStrRiChr();

        yield from $this->provideMutationCasesForConvertCase();
    }

    private function provideMutationCasesForChr(): Generator
    {
        yield 'It converts mb_chr to chr' => [
            '<?php mb_chr(74);',
            "<?php\n\nchr(74);",
        ];

        yield 'It converts mb_chr with encoding to chr' => [
            "<?php mb_chr(74, 'utf-8');",
            "<?php\n\nchr(74);",
        ];
    }

    private function provideMutationCasesForEregMatch(): Generator
    {
        yield 'It converts mb_ereg_match to preg_match' => [
            "<?php mb_ereg_match(M_2_PI, 'test');",
            "<?php\n\npreg_match('/^' . \str_replace('/', '\\\\/', M_2_PI) . '/', 'test') === 1;",
        ];

        yield 'It converts mb_ereg_match with encoding argument to preg_match' => [
            "<?php mb_ereg_match(M_2_PI, 'trim', 'mb');",
            "<?php\n\npreg_match('/^' . \str_replace('/', '\\\\/', M_2_PI) . '/', 'trim') === 1;",
        ];
    }

    private function provideMutationCasesForEregReplaceCallback(): Generator
    {
        yield 'It converts mb_ereg_replace_callback to preg_replace_callback' => [
            "<?php mb_ereg_replace_callback('[A-Z/#]', 'trim', 'test');",
            "<?php\n\npreg_replace_callback('/' . \str_replace('/', '\\\\/', '[A-Z/#]') . '/', 'trim', 'test');",
        ];

        yield 'It converts mb_ereg_replace_callback with options to preg_replace_callback' => [
            "<?php mb_ereg_replace_callback('[A-Z/#]', 'trim', 'test', 'msr');",
            "<?php\n\npreg_replace_callback('/' . \str_replace('/', '\\\\/', '[A-Z/#]') . '/', 'trim', 'test');",
        ];
    }

    private function provideMutationCasesForEregReplace(): Generator
    {
        yield 'It converts mb_ereg_replace to ereg_replace' => [
            "<?php mb_ereg_replace(\$pattern, 'T', 'test');",
            "<?php\n\npreg_replace('/' . \str_replace('/', '\\\\/', \$pattern) . '/', 'T', 'test');",
        ];

        yield 'It converts mb_ereg_replace with options to ereg_replace' => [
            "<?php mb_ereg_replace(\$pattern, 'T', 'test', 'mb');",
            "<?php\n\npreg_replace('/' . \str_replace('/', '\\\\/', \$pattern) . '/', 'T', 'test');",
        ];
    }

    private function provideMutationCasesForEreg(): Generator
    {
        yield 'It converts mb_ereg to ereg' => [
            "<?php mb_ereg(getPattern(\$x, 1), 'test');",
            "<?php\n\npreg_match('/' . \str_replace('/', '\\\\/', getPattern(\$x, 1)) . '/', 'test') ? 1 : false;",
        ];

        yield 'It converts mb_ereg with regs argument to ereg' => [
            "<?php mb_ereg(getPattern(\$x, 1), 'test', \$regs);",
            "<?php\n\npreg_match('/' . \str_replace('/', '\\\\/', getPattern(\$x, 1)) . '/', 'test', \$regs) ? 1 : false;",
        ];
    }

    private function provideMutationCasesForEregiReplace(): Generator
    {
        yield 'It converts mb_eregi_replace to eregi_replace' => [
            "<?php mb_eregi_replace(\$a . \$b, 'T', 'test');",
            "<?php\n\npreg_replace('/' . \str_replace('/', '\\\\/', \$a . \$b) . '/i', 'T', 'test');",
        ];

        yield 'It converts mb_eregi_replace with options to eregi_replace' => [
            "<?php mb_eregi_replace(\$a . \$b, 'T', 'test', 'msr');",
            "<?php\n\npreg_replace('/' . \str_replace('/', '\\\\/', \$a . \$b) . '/i', 'T', 'test');",
        ];
    }

    private function provideMutationCasesForEregi(): Generator
    {
        yield 'It converts mb_eregi to eregi' => [
            "<?php mb_eregi(\DateTime::getLastErrors(), 'test');",
            "<?php\n\npreg_match('/' . \str_replace('/', '\\\\/', \DateTime::getLastErrors()) . '/i', 'test') ? 1 : false;",
        ];

        yield 'It converts mb_eregi with regs parameter to eregi' => [
            "<?php mb_eregi(\DateTime::getLastErrors(), 'test', \$regs);",
            "<?php\n\npreg_match('/' . \str_replace('/', '\\\\/', \DateTime::getLastErrors()) . '/i', 'test', \$regs) ? 1 : false;",
        ];
    }

    private function provideMutationCasesForOrd(): Generator
    {
        yield 'It converts mb_ord to ord' => [
            "<?php mb_ord('T');",
            "<?php\n\nord('T');",
        ];

        yield 'It converts mb_ord with encoding to ord' => [
            "<?php mb_ord('T', 'utf-8');",
            "<?php\n\nord('T');",
        ];
    }

    private function provideMutationCasesForParseStr(): Generator
    {
        yield 'It converts mb_parse_str to parse_str' => [
            "<?php mb_parse_str('T');",
            "<?php\n\nparse_str('T');",
        ];

        yield 'It converts mb_parse_str with params argument to parse_str' => [
            "<?php mb_parse_str('T', \$params);",
            "<?php\n\nparse_str('T', \$params);",
        ];
    }

    private function provideMutationCasesForSendMail(): Generator
    {
        yield 'It converts mb_send_mail to mail' => [
            "<?php mb_send_mail('to', 'subject', 'msg');",
            "<?php\n\nmail('to', 'subject', 'msg');",
        ];

        yield 'It converts mb_send_mail with additional parameters to mail' => [
            "<?php mb_send_mail('to', 'subject', 'msg', [], []);",
            "<?php\n\nmail('to', 'subject', 'msg', [], []);",
        ];
    }

    private function provideMutationCasesForSplit(): Generator
    {
        yield 'It converts mb_split to split' => [
            "<?php mb_split('to', 'subject');",
            "<?php\n\nsplit('to', 'subject');",
        ];

        yield 'It converts mb_split with limit parameter to split' => [
            "<?php mb_split('to', 'subject', 2);",
            "<?php\n\nsplit('to', 'subject', 2);",
        ];
    }

    private function provideMutationCasesForStrCut(): Generator
    {
        yield 'It converts mb_strcut to substr' => [
            "<?php mb_strcut('subject', 1);",
            "<?php\n\nsubstr('subject', 1);",
        ];

        yield 'It converts mb_strcut with limit to substr' => [
            "<?php mb_strcut('subject', 1, 20);",
            "<?php\n\nsubstr('subject', 1, 20);",
        ];

        yield 'It converts mb_strcut with encoding to substr' => [
            "<?php mb_strcut('subject', 1, 20, 'utf-8');",
            "<?php\n\nsubstr('subject', 1, 20);",
        ];
    }

    private function provideMutationCasesForStrPos(): Generator
    {
        yield 'It converts mb_strpos to strpos' => [
            "<?php mb_strpos('subject', 'b');",
            "<?php\n\nstrpos('subject', 'b');",
        ];

        yield 'It converts mb_strpos with offset to strpos' => [
            "<?php mb_strpos('subject', 'b', 3);",
            "<?php\n\nstrpos('subject', 'b', 3);",
        ];

        yield 'It converts mb_strpos with encoding to strpos' => [
            "<?php mb_strpos('subject', 'b', 3, 'utf-8');",
            "<?php\n\nstrpos('subject', 'b', 3);",
        ];
    }

    private function provideMutationCasesForStrIPos(): Generator
    {
        yield 'It converts mb_stripos to stripos' => [
            "<?php mb_stripos('subject', 'b');",
            "<?php\n\nstripos('subject', 'b');",
        ];

        yield 'It converts mb_stripos with offset to stripos' => [
            "<?php mb_stripos('subject', 'b', 3);",
            "<?php\n\nstripos('subject', 'b', 3);",
        ];

        yield 'It converts mb_stripos with encoding to stripos' => [
            "<?php mb_stripos('subject', 'b', 3, 'utf-8');",
            "<?php\n\nstripos('subject', 'b', 3);",
        ];
    }

    private function provideMutationCasesForStrIStr(): Generator
    {
        yield 'It converts mb_stristr to stristr' => [
            "<?php mb_stristr('subject', 'b');",
            "<?php\n\nstristr('subject', 'b');",
        ];

        yield 'It converts mb_stristr with part argument to stristr' => [
            "<?php mb_stristr('subject', 'b', false);",
            "<?php\n\nstristr('subject', 'b', false);",
        ];

        yield 'It converts mb_stristr with encoding to stristr' => [
            "<?php mb_stristr('subject', 'b', false, 'utf-8');",
            "<?php\n\nstristr('subject', 'b', false);",
        ];
    }

    private function provideMutationCasesForStrRiPos(): Generator
    {
        yield 'It converts mb_strripos to strripos' => [
            "<?php mb_strripos('subject', 'b');",
            "<?php\n\nstrripos('subject', 'b');",
        ];

        yield 'It converts mb_strripos with offset argument to strripos' => [
            "<?php mb_strripos('subject', 'b', 2);",
            "<?php\n\nstrripos('subject', 'b', 2);",
        ];

        yield 'It converts mb_strripos with encoding to strripos' => [
            "<?php mb_strripos('subject', 'b', 2, 'utf-8');",
            "<?php\n\nstrripos('subject', 'b', 2);",
        ];
    }

    private function provideMutationCasesForStrRPos(): Generator
    {
        yield 'It converts mb_strrpos to strrpos' => [
            "<?php mb_strrpos('subject', 'b');",
            "<?php\n\nstrrpos('subject', 'b');",
        ];

        yield 'It converts mb_strrpos with offset argument to strrpos' => [
            "<?php mb_strrpos('subject', 'b', 2);",
            "<?php\n\nstrrpos('subject', 'b', 2);",
        ];

        yield 'It converts mb_strrpos with encoding to strrpos' => [
            "<?php mb_strrpos('subject', 'b', 2, 'utf-8');",
            "<?php\n\nstrrpos('subject', 'b', 2);",
        ];
    }

    private function provideMutationCasesForStrStr(): Generator
    {
        yield 'It converts mb_strstr to strstr' => [
            "<?php mb_strstr('subject', 'b');",
            "<?php\n\nstrstr('subject', 'b');",
        ];

        yield 'It converts mb_strstr with part argument to strstr' => [
            "<?php mb_strstr('subject', 'b', false);",
            "<?php\n\nstrstr('subject', 'b', false);",
        ];

        yield 'It converts mb_strstr with encoding to strstr' => [
            "<?php mb_strstr('subject', 'b', false, 'utf-8');",
            "<?php\n\nstrstr('subject', 'b', false);",
        ];
    }

    private function provideMutationCasesForStrToLower(): Generator
    {
        yield 'It converts mb_strtolower to strtolower' => [
            "<?php mb_strtolower('test');",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It converts mb_strtolower with encoding to strtolower' => [
            "<?php mb_strtolower('test', 'utf-8');",
            "<?php\n\nstrtolower('test');",
        ];
    }

    private function provideMutationCasesForStrToUpper(): Generator
    {
        yield 'It converts mb_strtoupper to strtoupper' => [
            "<?php mb_strtoupper('test');",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It converts mb_strtoupper with encoding to strtoupper' => [
            "<?php mb_strtoupper('test', 'utf-8');",
            "<?php\n\nstrtoupper('test');",
        ];
    }

    private function provideMutationCasesForSubStrCount(): Generator
    {
        yield 'It converts mb_substr_count to substr_count' => [
            "<?php mb_substr_count('test', 't');",
            "<?php\n\nsubstr_count('test', 't');",
        ];

        yield 'It converts mb_substr_count with encoding to substr_count' => [
            "<?php mb_substr_count('test', 't', 'utf-8');",
            "<?php\n\nsubstr_count('test', 't');",
        ];
    }

    private function provideMutationCasesForSubStr(): Generator
    {
        yield 'It converts mb_substr to substr' => [
            "<?php mb_substr('test', 2);",
            "<?php\n\nsubstr('test', 2);",
        ];

        yield 'It converts mb_substr with length argument to substr' => [
            "<?php mb_substr('test', 2, 10);",
            "<?php\n\nsubstr('test', 2, 10);",
        ];

        yield 'It converts mb_substr with encoding argument to substr' => [
            "<?php mb_substr('test', 2, 10, 'utf-8');",
            "<?php\n\nsubstr('test', 2, 10);",
        ];
    }

    private function provideMutationCasesForStrRChr(): Generator
    {
        yield 'It converts mb_strrchr to strrchr' => [
            "<?php mb_strrchr('subject', 'b');",
            "<?php\n\nstrrchr('subject', 'b');",
        ];

        yield 'It converts mb_strrchr with part argument to strrchr' => [
            "<?php mb_strrchr('subject', 'b', false);",
            "<?php\n\nstrrchr('subject', 'b');",
        ];

        yield 'It converts mb_strrchr with encoding to strrchr' => [
            "<?php mb_strrchr('subject', 'b', false, 'utf-8');",
            "<?php\n\nstrrchr('subject', 'b');",
        ];
    }

    private function provideMutationCasesForStrRiChr(): Generator
    {
        yield 'It converts mb_strrichr to strrchr' => [
            "<?php mb_strrichr('subject', 'b');",
            "<?php\n\nstrrchr('subject', 'b');",
        ];

        yield 'It converts mb_strrichr with part argument to strrchr' => [
            "<?php mb_strrichr('subject', 'b', false);",
            "<?php\n\nstrrchr('subject', 'b');",
        ];

        yield 'It converts mb_strrichr with encoding to strrchr' => [
            "<?php mb_strrichr('subject', 'b', false, 'utf-8');",
            "<?php\n\nstrrchr('subject', 'b');",
        ];
    }

    private function provideMutationCasesForConvertCase(): Generator
    {
        yield 'It converts mb_convert_case with MB_CASE_UPPER mode to strtoupper' => [
            "<?php mb_convert_case('test', MB_CASE_UPPER);",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It converts mb_convert_case with MB_CASE_UPPER_SIMPLE mode to strtoupper' => [
            "<?php mb_convert_case('test', MB_CASE_UPPER_SIMPLE);",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It converts mb_convert_case with MB_CASE_LOWER mode to strtolower' => [
            "<?php mb_convert_case('test', E_ERROR);",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It converts mb_convert_case with MB_CASE_LOWER_SIMPLE mode to strtolower' => [
            "<?php mb_convert_case('test', MB_CASE_LOWER);",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It converts mb_convert_case with MB_CASE_TITLE mode to ucwords' => [
            "<?php mb_convert_case('test', MB_CASE_TITLE);",
            "<?php\n\nucwords('test');",
        ];

        yield 'It converts mb_convert_case with MB_CASE_TITLE_SIMPLE mode to ucwords' => [
            "<?php mb_convert_case('test', \MB_CASE_TITLE_SIMPLE);",
            "<?php\n\nucwords('test');",
        ];

        yield 'It converts mb_convert_case with MB_CASE_FOLD mode to strtolower' => [
            "<?php mb_convert_case('test', MB_CASE_FOLD);",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It converts mb_convert_case with MB_CASE_FOLD_SIMPLE mode to strtolower' => [
            "<?php mb_convert_case('test', MB_CASE_FOLD_SIMPLE);",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It does not convert mb_convert_case with mode as variable' => [
            "<?php mb_convert_case('test', \$mode);",
        ];

        yield 'It does not convert mb_convert_case with missing mode argument' => [
            "<?php mb_convert_case('test');",
        ];
    }
}
