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

namespace Infection\Tests\Mutator\Extensions;

use function defined;
use Infection\Mutator\Extensions\MBString;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function Safe\define;

#[CoversClass(MBString::class)]
final class MBStringTest extends BaseMutatorTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::defineMissingMbCaseConstants();
    }

    /**
     * @param string|string[]|null $expected
     * @param array<string, bool> $settings
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array|null $expected = [], array $settings = []): void
    {
        $this->assertMutatesInput($input, $expected, $settings);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It converts mb_strlen with leading slash' => [
            self::wrapCodeInMethod("\mb_strlen('test');"),
            self::wrapCodeInMethod("strlen('test');"),
        ];

        yield 'It converts mb_strlen with leading slash in namespace' => [
            self::wrapCodeInMethod("\mb_strlen('test');", 'Test'),
            self::wrapCodeInMethod("strlen('test');", 'Test'),
        ];

        yield 'It does not convert standard functions like strpos' => [
            self::wrapCodeInMethod("strpos('test');"),
        ];

        yield 'It converts mb_strlen with encoding to strlen' => [
            self::wrapCodeInMethod("mb_strlen('test', 'utf-8');"),
            self::wrapCodeInMethod("strlen('test');"),
            ['mb_strlen' => true],
        ];

        yield 'It does not convert mb_strlen when disabled' => [
            self::wrapCodeInMethod("mb_strlen('test');"),
            null,
            ['mb_strlen' => false],
        ];

        yield from self::mutationsProviderForChr();

        yield from self::mutationsProviderForOrd();

        yield from self::mutationsProviderForParseStr();

        yield from self::mutationsProviderForSendMail();

        yield from self::mutationsProviderForStrCut();

        yield from self::mutationsProviderForStrPos();

        yield from self::mutationsProviderForStrIPos();

        yield from self::mutationsProviderForStrIStr();

        yield from self::mutationsProviderForStrRiPos();

        yield from self::mutationsProviderForStrRPos();

        yield from self::mutationsProviderForStrStr();

        yield from self::mutationsProviderForStrToLower();

        yield from self::mutationsProviderForStrToUpper();

        yield from self::mutationsProviderForSubStrCount();

        yield from self::mutationsProviderForSubStr();

        yield from self::mutationsProviderForStrRChr();

        yield from self::mutationsProviderForConvertCase();

        yield from self::mutationsProviderForStrSplit();
    }

    private static function mutationsProviderForChr(): iterable
    {
        yield 'It converts mb_chr to chr' => [
            self::wrapCodeInMethod('mb_chr(74);'),
            self::wrapCodeInMethod('chr(74);'),
        ];

        yield 'It converts correctly when mb_chr is wrongly capitalized' => [
            self::wrapCodeInMethod('mB_cHr(74);'),
            self::wrapCodeInMethod('chr(74);'),
        ];

        yield 'It converts mb_chr with encoding to chr' => [
            self::wrapCodeInMethod("mb_chr(74, 'utf-8');"),
            self::wrapCodeInMethod('chr(74);'),
        ];

        yield 'It does not mutate mb_chr called via variable' => [
            self::wrapCodeInMethod('$a = "mb_chr"; $a(74);'),
        ];
    }

    private static function mutationsProviderForOrd(): iterable
    {
        yield 'It converts mb_ord to ord' => [
            self::wrapCodeInMethod("mb_ord('T');"),
            self::wrapCodeInMethod("ord('T');"),
        ];

        yield 'It converts correctly when mb_ord is wrongly capitalized' => [
            self::wrapCodeInMethod("MB_ord('T');"),
            self::wrapCodeInMethod("ord('T');"),
        ];

        yield 'It converts mb_ord with encoding to ord' => [
            self::wrapCodeInMethod("mb_ord('T', 'utf-8');"),
            self::wrapCodeInMethod("ord('T');"),
        ];

        yield 'It does not mutate mb_ord called via variable' => [
            self::wrapCodeInMethod('$a = "mb_ord"; $a("T");'),
        ];
    }

    private static function mutationsProviderForParseStr(): iterable
    {
        yield 'It converts mb_parse_str to parse_str' => [
            self::wrapCodeInMethod("mb_parse_str('T');"),
            self::wrapCodeInMethod("parse_str('T');"),
        ];

        yield 'It converts correctly when mb_parse_str is wrongly capitalize' => [
            self::wrapCodeInMethod("mb_pARse_Str('T');"),
            self::wrapCodeInMethod("parse_str('T');"),
        ];

        yield 'It converts mb_parse_str with params argument to parse_str' => [
            self::wrapCodeInMethod("mb_parse_str('T', \$params);"),
            self::wrapCodeInMethod("parse_str('T', \$params);"),
        ];

        yield 'It does not mutate mb_parse_str called via variable' => [
            self::wrapCodeInMethod('$a = "mb_parse_str"; $a("T");'),
        ];
    }

    private static function mutationsProviderForSendMail(): iterable
    {
        yield 'It converts mb_send_mail to mail' => [
            self::wrapCodeInMethod("mb_send_mail('to', 'subject', 'msg');"),
            self::wrapCodeInMethod("mail('to', 'subject', 'msg');"),
        ];

        yield 'It converts correctly when mb_send_mail is wrongly capitalize' => [
            self::wrapCodeInMethod("mb_SEND_mail('to', 'subject', 'msg');"),
            self::wrapCodeInMethod("mail('to', 'subject', 'msg');"),
        ];

        yield 'It converts mb_send_mail with additional parameters to mail' => [
            self::wrapCodeInMethod("mb_send_mail('to', 'subject', 'msg', [], []);"),
            self::wrapCodeInMethod("mail('to', 'subject', 'msg', [], []);"),
        ];

        yield 'It does not mutate mb_send_mail called via variable' => [
            self::wrapCodeInMethod('$a = "mb_send_mail"; $a("to", "subject", "msg");'),
        ];
    }

    private static function mutationsProviderForStrCut(): iterable
    {
        yield 'It converts mb_strcut to substr' => [
            self::wrapCodeInMethod("mb_strcut('subject', 1);"),
            self::wrapCodeInMethod("substr('subject', 1);"),
        ];

        yield 'It converts correctly when mb_strcut is wrongly capitalize' => [
            self::wrapCodeInMethod("MB_strcut('subject', 1);"),
            self::wrapCodeInMethod("substr('subject', 1);"),
        ];

        yield 'It converts mb_strcut with limit to substr' => [
            self::wrapCodeInMethod("mb_strcut('subject', 1, 20);"),
            self::wrapCodeInMethod("substr('subject', 1, 20);"),
        ];

        yield 'It converts mb_strcut with encoding to substr' => [
            self::wrapCodeInMethod("mb_strcut('subject', 1, 20, 'utf-8');"),
            self::wrapCodeInMethod("substr('subject', 1, 20);"),
        ];

        yield 'It does not mutate mb_strcut called via variable' => [
            self::wrapCodeInMethod('$a = "mb_strcut"; $a("subject", 1);'),
        ];
    }

    private static function mutationsProviderForStrPos(): iterable
    {
        yield 'It converts mb_strpos to strpos' => [
            self::wrapCodeInMethod("mb_strpos('subject', 'b');"),
            self::wrapCodeInMethod("strpos('subject', 'b');"),
        ];

        yield 'It converts correctly when mb_strpos is wrongly capitalize' => [
            self::wrapCodeInMethod("mb_StRpOs('subject', 'b');"),
            self::wrapCodeInMethod("strpos('subject', 'b');"),
        ];

        yield 'It converts mb_strpos with offset to strpos' => [
            self::wrapCodeInMethod("mb_strpos('subject', 'b', 3);"),
            self::wrapCodeInMethod("strpos('subject', 'b', 3);"),
        ];

        yield 'It converts mb_strpos with encoding to strpos' => [
            self::wrapCodeInMethod("mb_strpos('subject', 'b', 3, 'utf-8');"),
            self::wrapCodeInMethod("strpos('subject', 'b', 3);"),
        ];

        yield 'It does not mutate mb_strpos called via variable' => [
            self::wrapCodeInMethod('$a = "mb_strpos"; $a("subject", "b");'),
        ];
    }

    private static function mutationsProviderForStrIPos(): iterable
    {
        yield 'It converts mb_stripos to stripos' => [
            self::wrapCodeInMethod("mb_stripos('subject', 'b');"),
            self::wrapCodeInMethod("stripos('subject', 'b');"),
        ];

        yield 'It converts correctly when mb_stripos is wrongly capitalize' => [
            self::wrapCodeInMethod("mB_sTRIpos('subject', 'b');"),
            self::wrapCodeInMethod("stripos('subject', 'b');"),
        ];

        yield 'It converts mb_stripos with offset to stripos' => [
            self::wrapCodeInMethod("mb_stripos('subject', 'b', 3);"),
            self::wrapCodeInMethod("stripos('subject', 'b', 3);"),
        ];

        yield 'It converts mb_stripos with encoding to stripos' => [
            self::wrapCodeInMethod("mb_stripos('subject', 'b', 3, 'utf-8');"),
            self::wrapCodeInMethod("stripos('subject', 'b', 3);"),
        ];

        yield 'It does not mutate mb_stripos called via variable' => [
            self::wrapCodeInMethod('$a = "mb_stripos"; $a("subject", "b");'),
        ];
    }

    private static function mutationsProviderForStrIStr(): iterable
    {
        yield 'It converts mb_stristr to stristr' => [
            self::wrapCodeInMethod("mb_stristr('subject', 'b');"),
            self::wrapCodeInMethod("stristr('subject', 'b');"),
        ];

        yield 'It converts correctly when mb_stristr is wrongly capitalize' => [
            self::wrapCodeInMethod("mb_strISTR('subject', 'b');"),
            self::wrapCodeInMethod("stristr('subject', 'b');"),
        ];

        yield 'It converts mb_stristr with part argument to stristr' => [
            self::wrapCodeInMethod("mb_stristr('subject', 'b', false);"),
            self::wrapCodeInMethod("stristr('subject', 'b', false);"),
        ];

        yield 'It converts mb_stristr with encoding to stristr' => [
            self::wrapCodeInMethod("mb_stristr('subject', 'b', false, 'utf-8');"),
            self::wrapCodeInMethod("stristr('subject', 'b', false);"),
        ];

        yield 'It does not mutate mb_stristr called via variable' => [
            self::wrapCodeInMethod('$a = "mb_stristr"; $a("subject", "b");'),
        ];
    }

    private static function mutationsProviderForStrRiPos(): iterable
    {
        yield 'It converts mb_strripos to strripos' => [
            self::wrapCodeInMethod("mb_strripos('subject', 'b');"),
            self::wrapCodeInMethod("strripos('subject', 'b');"),
        ];

        yield 'It converts correctly when mb_strripos is wrongly capitalize' => [
            self::wrapCodeInMethod("MB_sTrRipos('subject', 'b');"),
            self::wrapCodeInMethod("strripos('subject', 'b');"),
        ];

        yield 'It converts mb_strripos with offset argument to strripos' => [
            self::wrapCodeInMethod("mb_strripos('subject', 'b', 2);"),
            self::wrapCodeInMethod("strripos('subject', 'b', 2);"),
        ];

        yield 'It converts mb_strripos with encoding to strripos' => [
            self::wrapCodeInMethod("mb_strripos('subject', 'b', 2, 'utf-8');"),
            self::wrapCodeInMethod("strripos('subject', 'b', 2);"),
        ];

        yield 'It does not mutate mb_strripos called via variable' => [
            self::wrapCodeInMethod('$a = "mb_strripos"; $a("subject", "b");'),
        ];
    }

    private static function mutationsProviderForStrRPos(): iterable
    {
        yield 'It converts mb_strrpos to strrpos' => [
            self::wrapCodeInMethod("mb_strrpos('subject', 'b');"),
            self::wrapCodeInMethod("strrpos('subject', 'b');"),
        ];

        yield 'It converts correctly when mb_strrpos is wrongly capitalize' => [
            self::wrapCodeInMethod("mb_StRrPos('subject', 'b');"),
            self::wrapCodeInMethod("strrpos('subject', 'b');"),
        ];

        yield 'It converts mb_strrpos with offset argument to strrpos' => [
            self::wrapCodeInMethod("mb_strrpos('subject', 'b', 2);"),
            self::wrapCodeInMethod("strrpos('subject', 'b', 2);"),
        ];

        yield 'It converts mb_strrpos with encoding to strrpos' => [
            self::wrapCodeInMethod("mb_strrpos('subject', 'b', 2, 'utf-8');"),
            self::wrapCodeInMethod("strrpos('subject', 'b', 2);"),
        ];

        yield 'It does not mutate mb_strrpos called via variable' => [
            self::wrapCodeInMethod('$a = "mb_strrpos"; $a("subject", "b");'),
        ];
    }

    private static function mutationsProviderForStrStr(): iterable
    {
        yield 'It converts mb_strstr to strstr' => [
            self::wrapCodeInMethod("mb_strstr('subject', 'b');"),
            self::wrapCodeInMethod("strstr('subject', 'b');"),
        ];

        yield 'It converts correctly when mb_strstr is wrongly capitalize' => [
            self::wrapCodeInMethod("Mb_STRstr('subject', 'b');"),
            self::wrapCodeInMethod("strstr('subject', 'b');"),
        ];

        yield 'It converts mb_strstr with part argument to strstr' => [
            self::wrapCodeInMethod("mb_strstr('subject', 'b', false);"),
            self::wrapCodeInMethod("strstr('subject', 'b', false);"),
        ];

        yield 'It converts mb_strstr with encoding to strstr' => [
            self::wrapCodeInMethod("mb_strstr('subject', 'b', false, 'utf-8');"),
            self::wrapCodeInMethod("strstr('subject', 'b', false);"),
        ];

        yield 'It does not mutate mb_strstr called via variable' => [
            self::wrapCodeInMethod('$a = "mb_strstr"; $a("subject", "b");'),
        ];
    }

    private static function mutationsProviderForStrToLower(): iterable
    {
        yield 'It converts mb_strtolower to strtolower' => [
            self::wrapCodeInMethod("mb_strtolower('test');"),
            self::wrapCodeInMethod("strtolower('test');"),
        ];

        yield 'It converts correctly when mb_strtolower is wrongly capitalize' => [
            self::wrapCodeInMethod("mB_StrTOloWer('test');"),
            self::wrapCodeInMethod("strtolower('test');"),
        ];

        yield 'It converts mb_strtolower with encoding to strtolower' => [
            self::wrapCodeInMethod("mb_strtolower('test', 'utf-8');"),
            self::wrapCodeInMethod("strtolower('test');"),
        ];

        yield 'It does not mutate mb_strtolower called via variable' => [
            self::wrapCodeInMethod('$a = "mb_strtolower"; $a("test");'),
        ];
    }

    private static function mutationsProviderForStrToUpper(): iterable
    {
        yield 'It converts mb_strtoupper to strtoupper' => [
            self::wrapCodeInMethod("mb_strtoupper('test');"),
            self::wrapCodeInMethod("strtoupper('test');"),
        ];

        yield 'It converts correctly when mb_strtoupper is wrongly capitalize' => [
            self::wrapCodeInMethod("Mb_StrToupPer('test');"),
            self::wrapCodeInMethod("strtoupper('test');"),
        ];

        yield 'It converts mb_strtoupper with encoding to strtoupper' => [
            self::wrapCodeInMethod("mb_strtoupper('test', 'utf-8');"),
            self::wrapCodeInMethod("strtoupper('test');"),
        ];

        yield 'It does not mutate mb_strtoupper called via variable' => [
            self::wrapCodeInMethod('$a = "mb_strtoupper"; $a("test");'),
        ];
    }

    private static function mutationsProviderForSubStrCount(): iterable
    {
        yield 'It converts mb_substr_count to substr_count' => [
            self::wrapCodeInMethod("mb_substr_count('test', 't');"),
            self::wrapCodeInMethod("substr_count('test', 't');"),
        ];

        yield 'It converts correctly when mb_substr_count is wrongly capitalize' => [
            self::wrapCodeInMethod("MB_substr_COunt('test', 't');"),
            self::wrapCodeInMethod("substr_count('test', 't');"),
        ];

        yield 'It converts mb_substr_count with encoding to substr_count' => [
            self::wrapCodeInMethod("mb_substr_count('test', 't', 'utf-8');"),
            self::wrapCodeInMethod("substr_count('test', 't');"),
        ];

        yield 'It does not mutate mb_substr_count called via variable' => [
            '<?php $a = "mb_substr_count"; $a("test", "t");',
        ];
    }

    private static function mutationsProviderForSubStr(): iterable
    {
        yield 'It converts mb_substr to substr' => [
            self::wrapCodeInMethod("mb_substr('test', 2);"),
            self::wrapCodeInMethod("substr('test', 2);"),
        ];

        yield 'It converts correctly when mb_substr is wrongly capitalize' => [
            self::wrapCodeInMethod("mB_SuBsTr('test', 2);"),
            self::wrapCodeInMethod("substr('test', 2);"),
        ];

        yield 'It converts mb_substr with length argument to substr' => [
            self::wrapCodeInMethod("mb_substr('test', 2, 10);"),
            self::wrapCodeInMethod("substr('test', 2, 10);"),
        ];

        yield 'It converts mb_substr with encoding argument to substr' => [
            self::wrapCodeInMethod("mb_substr('test', 2, 10, 'utf-8');"),
            self::wrapCodeInMethod("substr('test', 2, 10);"),
        ];

        yield 'It does not mutate mb_substr called via variable' => [
            '<?php $a = "mb_substr"; $a("test", 2, 10);',
        ];
    }

    private static function mutationsProviderForStrRChr(): iterable
    {
        yield 'It converts mb_strrchr to strrchr' => [
            self::wrapCodeInMethod("mb_strrchr('subject', 'b');"),
            self::wrapCodeInMethod("strrchr('subject', 'b');"),
        ];

        yield 'It converts correctly when mb_strrchr is wrongly capitalize' => [
            self::wrapCodeInMethod("MB_StRrcHr('subject', 'b');"),
            self::wrapCodeInMethod("strrchr('subject', 'b');"),
        ];

        yield 'It converts mb_strrchr with part argument to strrchr' => [
            self::wrapCodeInMethod("mb_strrchr('subject', 'b', false);"),
            self::wrapCodeInMethod("strrchr('subject', 'b');"),
        ];

        yield 'It converts mb_strrchr with encoding to strrchr' => [
            self::wrapCodeInMethod("mb_strrchr('subject', 'b', false, 'utf-8');"),
            self::wrapCodeInMethod("strrchr('subject', 'b');"),
        ];

        yield 'It does not mutate mb_strrchr called via variable' => [
            '<?php $a = "mb_strrchr"; $a("subject", "b");',
        ];
    }

    private static function mutationsProviderForConvertCase(): iterable
    {
        yield 'It converts mb_convert_case with MB_CASE_UPPER mode to strtoupper' => [
            self::wrapCodeInMethod("mb_convert_case('test', MB_CASE_UPPER);"),
            self::wrapCodeInMethod("strtoupper('test');"),
        ];

        yield 'It converts correctly when mb_convert_case is wrongly capitalize' => [
            self::wrapCodeInMethod("Mb_CoNvErT_Case('test', MB_CASE_UPPER);"),
            self::wrapCodeInMethod("strtoupper('test');"),
        ];

        yield 'It converts mb_convert_case with MB_CASE_UPPER_SIMPLE mode to strtoupper' => [
            self::wrapCodeInMethod("mb_convert_case('test', MB_CASE_UPPER_SIMPLE);"),
            self::wrapCodeInMethod("strtoupper('test');"),
        ];

        yield 'It converts mb_convert_case with constant similar MB_CASE_LOWER mode to strtolower' => [
            self::wrapCodeInMethod("mb_convert_case('test', E_ERROR);"),
            self::wrapCodeInMethod("strtolower('test');"),
        ];

        yield 'It converts mb_convert_case with numeric MB_CASE_LOWER (1) mode to strtolower' => [
            self::wrapCodeInMethod("mb_convert_case('test', 1);"),
            self::wrapCodeInMethod("strtolower('test');"),
        ];

        yield 'It converts mb_convert_case with MB_CASE_LOWER_SIMPLE mode to strtolower' => [
            self::wrapCodeInMethod("mb_convert_case('test', MB_CASE_LOWER);"),
            self::wrapCodeInMethod("strtolower('test');"),
        ];

        yield 'It converts mb_convert_case with MB_CASE_TITLE mode to ucwords' => [
            self::wrapCodeInMethod("mb_convert_case('test', MB_CASE_TITLE);"),
            self::wrapCodeInMethod("ucwords('test');"),
        ];

        yield 'It converts mb_convert_case with MB_CASE_TITLE_SIMPLE mode to ucwords' => [
            self::wrapCodeInMethod("mb_convert_case('test', \MB_CASE_TITLE_SIMPLE);"),
            self::wrapCodeInMethod("ucwords('test');"),
        ];

        yield 'It converts mb_convert_case with MB_CASE_FOLD mode to strtolower' => [
            self::wrapCodeInMethod("mb_convert_case('test', MB_CASE_FOLD);"),
            self::wrapCodeInMethod("strtolower('test');"),
        ];

        yield 'It converts mb_convert_case with MB_CASE_FOLD_SIMPLE mode to strtolower' => [
            self::wrapCodeInMethod("mb_convert_case('test', MB_CASE_FOLD_SIMPLE);"),
            self::wrapCodeInMethod("strtolower('test');"),
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

    private static function mutationsProviderForStrSplit(): iterable
    {
        yield 'It converts mb_str_split to str_split' => [
            self::wrapCodeInMethod("mb_str_split('test', 2);"),
            self::wrapCodeInMethod("str_split('test', 2);"),
        ];

        yield 'It converts correctly when mb_str_split is wrongly capitalize' => [
            self::wrapCodeInMethod("MB_str_sPlit('test', 2);"),
            self::wrapCodeInMethod("str_split('test', 2);"),
        ];

        yield 'It converts mb_str_split with encoding to str_split' => [
            self::wrapCodeInMethod("mb_str_split('test', 2, 'utf-8');"),
            self::wrapCodeInMethod("str_split('test', 2);"),
        ];

        yield 'It does not mutate mb_str_split called via variable' => [
            '<?php $a = "mb_str_split"; $a("test", 2);',
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
            if (!defined($constantName)) {
                define($constantName, $constantValue);
            }
        }
    }
}
