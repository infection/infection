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

namespace Infection\Tests\AutoReview\EnvVariableManipulation;

use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Infection\Tests\AutoReview\EnvVariableManipulation\EnvManipulatorCodeDetector
 */
final class EnvManipulatorCodeDetectorTest extends TestCase
{
    /**
     * @dataProvider codeProvider
     */
    public function test_it_can_detect_environment_variable_manipulations(string $code, bool $expected): void
    {
        $actual = EnvManipulatorCodeDetector::codeManipulatesEnvVariables($code);

        $this->assertSame($expected, $actual);
    }

    public function codeProvider(): Generator
    {
        yield 'empty' => [
            '',
            false,
        ];

        yield 'putenv core function' => [
            <<<'PHP'
<?php
putenv("FOO=BAR");
PHP
            ,
            false,   // Cannot detect this case since this is not a FQ call
        ];

        yield 'putenv core function FQ call' => [
            <<<'PHP'
<?php
\putenv("FOO=BAR");
PHP
            ,
            true,
        ];

        yield 'putenv core function imported' => [
            <<<'PHP'
<?php
use function putenv;
PHP
            ,
            true,
        ];

        yield 'putenv Safe function' => [
            <<<'PHP'
<?php
Safe\putenv('FOO=BAR');
PHP
            ,
            true,
        ];

        yield 'putenv Safe function FQ call' => [
            <<<'PHP'
<?php
\Safe\putenv('FOO=BAR');
PHP
            ,
            true,
        ];

        yield 'putenv Safe function imported' => [
            <<<'PHP'
<?php
use function Safe\putenv;
PHP
            ,
            true,
        ];

        yield 'readonly env function' => [
            <<<'PHP'
<?php
getenv('FOO');
PHP
            ,
            false,
        ];

        yield 'Statement containing a word match of a FS function' => [
            <<<'PHP'
<?php

/**
 * putenv
 */
PHP
            ,
            false,
        ];
    }
}
