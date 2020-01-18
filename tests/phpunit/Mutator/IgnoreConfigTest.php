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

namespace Infection\Tests\Mutator;

use Generator;
use Infection\Mutator\IgnoreConfig;
use PHPUnit\Framework\TestCase;

final class IgnoreConfigTest extends TestCase
{
    /**
     * @dataProvider ignoredValuesProvider
     */
    public function test_it_can_check_that_the_given_elements_are_ignored(
        array $ignored,
        string $class,
        string $method,
        ?int $lineNumber
    ): void {
        $config = new IgnoreConfig($ignored);

        $this->assertTrue($config->isIgnored($class, $method, $lineNumber));
    }

    /**
     * @dataProvider nonIgnoredValuesProvider
     */
    public function test_it_can_check_that_the_given_elements_are_not_ignored(
        array $ignored,
        string $class,
        string $method,
        ?int $lineNumber
    ): void {
        $config = new IgnoreConfig($ignored);

        $this->assertFalse($config->isIgnored($class, $method, $lineNumber));
    }

    public function ignoredValuesProvider(): Generator
    {
        yield 'full class' => [
            ['Foo\Bar\Test'],
            'Foo\Bar\Test',
            'method',
            null,
        ];

        yield 'full class with method' => [
            ['Foo\Bar\Test::method'],
            'Foo\Bar\Test',
            'method',
            null,
        ];

        yield 'pattern of a class' => [
            ['Foo\*\Test'],
            'Foo\Bar\Test',
            'method',
            null,
        ];

        yield 'all classes in the namespace' => [
            ['Foo\Test\*'],
            'Foo\Test\Baz',
            'method',
            null,
        ];

        yield 'pattern of a class with method' => [
            ['Foo\*::method'],
            'Foo\Bar\Test',
            'method',
            null,
        ];

        yield 'pattern of a method' => [
            ['Foo\Bar\Test::m?th?d'],
            'Foo\Bar\Test',
            'method',
            null,
        ];

        yield 'specific line number' => [
            ['Foo\Bar\Test::method::63'],
            'Foo\Bar\Test',
            'method',
            63,
        ];
    }

    public function nonIgnoredValuesProvider(): Generator
    {
        yield 'full class when the methods dont match' => [
            ['Foo\Bar\Test::otherMethod'],
            'Foo\Bar\Test',
            'method',
            null,
        ];

        yield 'class if casing doesnt match' => [
            ['FoO\BAr\tEst'],
            'Foo\Bar\Test',
            'method',
            null,
        ];

        yield 'pattern of a class if the method does not match' => [
            ['Foo\*\Test::other'],
            'Foo\Bar\Test',
            'method',
            null,
        ];

        yield 'pattern of a class with method if the class doesnt match' => [
            ['Foo\*::method'],
            'Bar\Foo\Test',
            'method',
            null,
        ];
    }
}
