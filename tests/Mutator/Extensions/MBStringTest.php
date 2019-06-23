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
    public static function setUpBeforeClass(): void
    {
        self::defineMissingMbCaseConstants();
    }

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

        yield from $this->provideMutationCasesForOrd();

        yield from $this->provideMutationCasesForParseStr();

        yield from $this->provideMutationCasesForSendMail();

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

        yield from $this->provideMutationCasesForConvertCase();
    }

    private function provideMutationCasesForChr(): Generator
    {
        yield 'It converts mb_chr to chr' => [
            '<?php mb_chr(74);',
            "<?php\n\nchr(74);",
        ];

        yield 'It converts correctly when mb_chr is wrongly capitalized' => [
            '<?php mB_cHr(74);',
            "<?php\n\nchr(74);",
        ];

        yield 'It converts mb_chr with encoding to chr' => [
            "<?php mb_chr(74, 'utf-8');",
            "<?php\n\nchr(74);",
        ];

        yield 'It does not mutate mb_chr called via variable' => [
            '<?php $a = "mb_chr"; $a(74);',
        ];
    }

    private function provideMutationCasesForOrd(): Generator
    {
        yield 'It converts mb_ord to ord' => [
            "<?php mb_ord('T');",
            "<?php\n\nord('T');",
        ];

        yield 'It converts correctly when mb_ord is wrongly capitalized' => [
            "<?php MB_ord('T');",
            "<?php\n\nord('T');",
        ];

        yield 'It converts mb_ord with encoding to ord' => [
            "<?php mb_ord('T', 'utf-8');",
            "<?php\n\nord('T');",
        ];

        yield 'It does not mutate mb_ord called via variable' => [
            '<?php $a = "mb_ord"; $a("T");',
        ];
    }

    private function provideMutationCasesForParseStr(): Generator
    {
        yield 'It converts mb_parse_str to parse_str' => [
            "<?php mb_parse_str('T');",
            "<?php\n\nparse_str('T');",
        ];

        yield 'It converts correctly when mb_parse_str is wrongly capitalize' => [
            "<?php mb_pARse_Str('T');",
            "<?php\n\nparse_str('T');",
        ];

        yield 'It converts mb_parse_str with params argument to parse_str' => [
            "<?php mb_parse_str('T', \$params);",
            "<?php\n\nparse_str('T', \$params);",
        ];

        yield 'It does not mutate mb_parse_str called via variable' => [
            '<?php $a = "mb_parse_str"; $a("T");',
        ];
    }

    private function provideMutationCasesForSendMail(): Generator
    {
        yield 'It converts mb_send_mail to mail' => [
            "<?php mb_send_mail('to', 'subject', 'msg');",
            "<?php\n\nmail('to', 'subject', 'msg');",
        ];

        yield 'It converts correctly when mb_send_mail is wrongly capitalize' => [
            "<?php mb_SEND_mail('to', 'subject', 'msg');",
            "<?php\n\nmail('to', 'subject', 'msg');",
        ];

        yield 'It converts mb_send_mail with additional parameters to mail' => [
            "<?php mb_send_mail('to', 'subject', 'msg', [], []);",
            "<?php\n\nmail('to', 'subject', 'msg', [], []);",
        ];

        yield 'It does not mutate mb_send_mail called via variable' => [
            '<?php $a = "mb_send_mail"; $a("to", "subject", "msg");',
        ];
    }

    private function provideMutationCasesForStrCut(): Generator
    {
        yield 'It converts mb_strcut to substr' => [
            "<?php mb_strcut('subject', 1);",
            "<?php\n\nsubstr('subject', 1);",
        ];

        yield 'It converts correctly when mb_strcut is wrongly capitalize' => [
            "<?php MB_strcut('subject', 1);",
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

        yield 'It does not mutate mb_strcut called via variable' => [
            '<?php $a = "mb_strcut"; $a("subject", 1);',
        ];
    }

    private function provideMutationCasesForStrPos(): Generator
    {
        yield 'It converts mb_strpos to strpos' => [
            "<?php mb_strpos('subject', 'b');",
            "<?php\n\nstrpos('subject', 'b');",
        ];

        yield 'It converts correctly when mb_strpos is wrongly capitalize' => [
            "<?php mb_StRpOs('subject', 'b');",
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

        yield 'It does not mutate mb_strpos called via variable' => [
            '<?php $a = "mb_strpos"; $a("subject", "b");',
        ];
    }

    private function provideMutationCasesForStrIPos(): Generator
    {
        yield 'It converts mb_stripos to stripos' => [
            "<?php mb_stripos('subject', 'b');",
            "<?php\n\nstripos('subject', 'b');",
        ];

        yield 'It converts correctly when mb_stripos is wrongly capitalize' => [
            "<?php mB_sTRIpos('subject', 'b');",
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

        yield 'It does not mutate mb_stripos called via variable' => [
            '<?php $a = "mb_stripos"; $a("subject", "b");',
        ];
    }

    private function provideMutationCasesForStrIStr(): Generator
    {
        yield 'It converts mb_stristr to stristr' => [
            "<?php mb_stristr('subject', 'b');",
            "<?php\n\nstristr('subject', 'b');",
        ];

        yield 'It converts correctly when mb_stristr is wrongly capitalize' => [
            "<?php mb_strISTR('subject', 'b');",
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

        yield 'It does not mutate mb_stristr called via variable' => [
            '<?php $a = "mb_stristr"; $a("subject", "b");',
        ];
    }

    private function provideMutationCasesForStrRiPos(): Generator
    {
        yield 'It converts mb_strripos to strripos' => [
            "<?php mb_strripos('subject', 'b');",
            "<?php\n\nstrripos('subject', 'b');",
        ];

        yield 'It converts correctly when mb_strripos is wrongly capitalize' => [
            "<?php MB_sTrRipos('subject', 'b');",
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

        yield 'It does not mutate mb_strripos called via variable' => [
            '<?php $a = "mb_strripos"; $a("subject", "b");',
        ];
    }

    private function provideMutationCasesForStrRPos(): Generator
    {
        yield 'It converts mb_strrpos to strrpos' => [
            "<?php mb_strrpos('subject', 'b');",
            "<?php\n\nstrrpos('subject', 'b');",
        ];

        yield 'It converts correctly when mb_strrpos is wrongly capitalize' => [
            "<?php mb_StRrPos('subject', 'b');",
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

        yield 'It does not mutate mb_strrpos called via variable' => [
            '<?php $a = "mb_strrpos"; $a("subject", "b");',
        ];
    }

    private function provideMutationCasesForStrStr(): Generator
    {
        yield 'It converts mb_strstr to strstr' => [
            "<?php mb_strstr('subject', 'b');",
            "<?php\n\nstrstr('subject', 'b');",
        ];

        yield 'It converts correctly when mb_strstr is wrongly capitalize' => [
            "<?php Mb_STRstr('subject', 'b');",
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

        yield 'It does not mutate mb_strstr called via variable' => [
            '<?php $a = "mb_strstr"; $a("subject", "b");',
        ];
    }

    private function provideMutationCasesForStrToLower(): Generator
    {
        yield 'It converts mb_strtolower to strtolower' => [
            "<?php mb_strtolower('test');",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It converts correctly when mb_strtolower is wrongly capitalize' => [
            "<?php mB_StrTOloWer('test');",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It converts mb_strtolower with encoding to strtolower' => [
            "<?php mb_strtolower('test', 'utf-8');",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It does not mutate mb_strtolower called via variable' => [
            '<?php $a = "mb_strtolower"; $a("test");',
        ];
    }

    private function provideMutationCasesForStrToUpper(): Generator
    {
        yield 'It converts mb_strtoupper to strtoupper' => [
            "<?php mb_strtoupper('test');",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It converts correctly when mb_strtoupper is wrongly capitalize' => [
            "<?php Mb_StrToupPer('test');",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It converts mb_strtoupper with encoding to strtoupper' => [
            "<?php mb_strtoupper('test', 'utf-8');",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It does not mutate mb_strtoupper called via variable' => [
            '<?php $a = "mb_strtoupper"; $a("test");',
        ];
    }

    private function provideMutationCasesForSubStrCount(): Generator
    {
        yield 'It converts mb_substr_count to substr_count' => [
            "<?php mb_substr_count('test', 't');",
            "<?php\n\nsubstr_count('test', 't');",
        ];

        yield 'It converts correctly when mb_substr_count is wrongly capitalize' => [
            "<?php MB_substr_COunt('test', 't');",
            "<?php\n\nsubstr_count('test', 't');",
        ];

        yield 'It converts mb_substr_count with encoding to substr_count' => [
            "<?php mb_substr_count('test', 't', 'utf-8');",
            "<?php\n\nsubstr_count('test', 't');",
        ];

        yield 'It does not mutate mb_substr_count called via variable' => [
            '<?php $a = "mb_substr_count"; $a("test", "t");',
        ];
    }

    private function provideMutationCasesForSubStr(): Generator
    {
        yield 'It converts mb_substr to substr' => [
            "<?php mb_substr('test', 2);",
            "<?php\n\nsubstr('test', 2);",
        ];

        yield 'It converts correctly when mb_substr is wrongly capitalize' => [
            "<?php mB_SuBsTr('test', 2);",
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

        yield 'It does not mutate mb_substr called via variable' => [
            '<?php $a = "mb_substr"; $a("test", 2, 10);',
        ];
    }

    private function provideMutationCasesForStrRChr(): Generator
    {
        yield 'It converts mb_strrchr to strrchr' => [
            "<?php mb_strrchr('subject', 'b');",
            "<?php\n\nstrrchr('subject', 'b');",
        ];

        yield 'It converts correctly when mb_strrchr is wrongly capitalize' => [
            "<?php MB_StRrcHr('subject', 'b');",
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

        yield 'It does not mutate mb_strrchr called via variable' => [
            '<?php $a = "mb_strrchr"; $a("subject", "b");',
        ];
    }

    private function provideMutationCasesForConvertCase(): Generator
    {
        yield 'It converts mb_convert_case with MB_CASE_UPPER mode to strtoupper' => [
            "<?php mb_convert_case('test', MB_CASE_UPPER);",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It converts correctly when mb_convert_case is wrongly capitalize' => [
            "<?php Mb_CoNvErT_Case('test', MB_CASE_UPPER);",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It converts mb_convert_case with MB_CASE_UPPER_SIMPLE mode to strtoupper' => [
            "<?php mb_convert_case('test', MB_CASE_UPPER_SIMPLE);",
            "<?php\n\nstrtoupper('test');",
        ];

        yield 'It converts mb_convert_case with constant similar MB_CASE_LOWER mode to strtolower' => [
            "<?php mb_convert_case('test', E_ERROR);",
            "<?php\n\nstrtolower('test');",
        ];

        yield 'It converts mb_convert_case with numeric MB_CASE_LOWER (1) mode to strtolower' => [
            "<?php mb_convert_case('test', 1);",
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

        yield 'It does not mutate mb_convert_case called via variable' => [
            '<?php $a = "mb_convert_case"; $a("test");',
        ];
    }

    private static function defineMissingMbCaseConstants(): void
    {
        foreach ([
            'MB_CASE_FOLD' => 3,
            'MB_CASE_UPPER_SIMPLE' => 4,
            'MB_CASE_LOWER_SIMPLE' => 5,
            'MB_CASE_TITLE_SIMPLE' => 6,
            'MB_CASE_FOLD_SIMPLE' => 7,
        ] as $constantName => $constantValue) {
            if (!\defined($constantName)) {
                \define($constantName, $constantValue);
            }
        }
    }
}
