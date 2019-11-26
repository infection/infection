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

namespace Infection\Tests\TestFramework\Codeception;

use Infection\TestFramework\Codeception\Stringifier;
use PHPUnit\Framework\TestCase;

final class StringifierTest extends TestCase
{
    /**
     * @var Stringifier
     */
    private $stringifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stringifier = new Stringifier();
    }

    /**
     * @dataProvider provideBooleanStrings
     */
    public function test_stringify_boolean(bool $boolean, string $expectedStringBoolean): void
    {
        $this->assertSame($expectedStringBoolean, $this->stringifier->stringifyBoolean($boolean));
    }

    /**
     * @dataProvider provideArrayOfStrings
     */
    public function test_stringify_array_of_strings(array $arrayOfStrings, string $expectedStringArray): void
    {
        $this->assertSame($expectedStringArray, $this->stringifier->stringifyArray($arrayOfStrings));
    }

    public function test_stringify_array_of_strings_works_only_with_array_of_strings(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $arrayOfInts = [1, 2, 3];

        $this->stringifier->stringifyArray($arrayOfInts);
    }

    public function provideBooleanStrings(): \Generator
    {
        yield 'True' => [true, 'true'];

        yield 'False' => [false, 'false'];
    }

    public function provideArrayOfStrings(): \Generator
    {
        yield 'Empty array' => [[], '[]'];

        yield 'One element' => [['/path/to/first'], '[/path/to/first]'];

        yield 'Several elements' => [['/path/to/first', '/second'], '[/path/to/first,/second]'];
    }
}
