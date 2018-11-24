<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Tests\Mutator\Util;

use Infection\Mutator\Util\MutatorConfig;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutatorConfigTest extends TestCase
{
    /**
     * @dataProvider providesIgnoredValues
     */
    public function test_is_ignored_returns_true_if_there_is_a_match(array $ignored, string $class, string $method): void
    {
        $config = new MutatorConfig(['ignore' => $ignored]);

        $this->assertTrue($config->isIgnored($class, $method));
    }

    public function providesIgnoredValues(): \Generator
    {
        yield 'It ignores a full class' => [
            ['Foo\Bar\Test'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It ignores a full class with method' => [
            ['Foo\Bar\Test::method'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It ignores a pattern of a class' => [
            ['Foo\*\Test'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It ignores all classes in the namespace' => [
            ['Foo\Test\*'],
            'Foo\Test\Baz',
            'method',
        ];

        yield 'It ignores a pattern of a class with method' => [
            ['Foo\*::method'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It ignores a pattern of a method' => [
            ['Foo\Bar\Test::m?th?d'],
            'Foo\Bar\Test',
            'method',
        ];
    }

    /**
     * @dataProvider providesNotIgnoredValues
     */
    public function test_is_ignored_returns_false_if_there_is_no_match(array $ignored, string $class, string $method): void
    {
        $config = new MutatorConfig(['ignore' => $ignored]);

        $this->assertFalse($config->isIgnored($class, $method));
    }

    public function providesNotIgnoredValues(): \Generator
    {
        yield 'It does not ignores a full class when the methods dont match' => [
            ['Foo\Bar\Test::otherMethod'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It does not ignore a class if casing doesnt match' => [
            ['FoO\BAr\tEst'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It does not ignore a pattern of a class if the method does not match' => [
            ['Foo\*\Test::other'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It does ignores a pattern of a class with method if the class doesnt match' => [
            ['Foo\*::method'],
            'Bar\Foo\Test',
            'method',
        ];
    }
}
