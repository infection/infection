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

namespace Infection\Tests\Mutator\Regex;

use Generator;
use Infection\Tests\Mutator\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
final class PregMatchRemoveCaretTest extends BaseMutatorTestCase
{
    #[DataProvider('provideMutationCases')]
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public static function provideMutationCases(): Generator
    {
        yield 'It mutates correctly removing caret when provided with a string and flags' => [
            <<<'PHP'
                <?php

                preg_match('~^some-regexp~ig', 'irrelevant');
                PHP
            ,
            <<<'PHP'
                <?php

                preg_match('~some-regexp~ig', 'irrelevant');
                PHP,
        ];

        yield 'It mutates correctly removing dollar when preg_match function is wrongly capitalized' => [
            <<<'PHP'
                <?php

                pReG_MaTcH('~^some-regexp~ig', 'irrelevant');
                PHP
            ,
            <<<'PHP'
                <?php

                pReG_MaTcH('~some-regexp~ig', 'irrelevant');
                PHP,
        ];

        yield 'It mutates correctly removing caret when provided with a string and without flags' => [
            <<<'PHP'
                <?php

                preg_match('~^some-regexp~', 'irrelevant');
                PHP
            ,
            <<<'PHP'
                <?php

                preg_match('~some-regexp~', 'irrelevant');
                PHP,
        ];

        yield 'It mutates correctly removing caret when delimiter is not standard' => [
            <<<'PHP'
                <?php

                preg_match('$^some-regexp$i', 'irrelevant');
                PHP
            ,
            <<<'PHP'
                <?php

                preg_match('$some-regexp$i', 'irrelevant');
                PHP,
        ];

        yield 'It does not mutate regular expression with a an encapsed variable' => [
            <<<'PHP'
                <?php

                preg_match("/^-\s*{$regexWithEscapedDelimiters}$/mu", $diff);
                PHP
            ,
        ];

        yield 'It does not mutate regular expression when no "^" is present in the beginning' => [
            <<<'PHP'
                <?php

                preg_match('~some-regexp~ig', 'irrelevant');
                PHP
            ,
        ];

        yield 'It does not mutate regular expression when "^" is used as an exact character' => [
            <<<'PHP'
                <?php

                preg_match('~some-reg\^exp~ig', 'irrelevant');
                PHP
            ,
        ];

        yield 'It does not mutate regular expression when provided with a variable' => [
            <<<'PHP'
                <?php

                preg_match($regex, 'irrelevant');
                PHP
            ,
        ];

        yield 'It does not mutate regular expression when provided with an unpacked array' => [
            <<<'PHP'
                <?php

                preg_match(...foo());
                PHP
            ,
        ];

        yield 'It does not mutate when provided with a variable function name' => [
            <<<'PHP'
                <?php

                $f = 'preg_match';

                $f('~^some-regexp$~ig', 'irrelevant');
                PHP
            ,
        ];
    }
}
