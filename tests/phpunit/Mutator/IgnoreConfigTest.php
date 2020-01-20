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

/**
 * @group integration This is probably a false-positive of the IO checker regarding `fnmatch()`
 */
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
        foreach ([null, 50] as $lineNumber) {
            $titleSuffix = null === $lineNumber ? '' : ' with line number #' . $lineNumber;

            yield 'full class' . $titleSuffix => [
                ['Acme\FooTest'],
                'Acme\FooTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'full class with method' . $titleSuffix => [
                ['Acme\FooTest::test_it_can_create_instance'],
                'Acme\FooTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'pattern of a class' . $titleSuffix => [
                ['Acme\*\FooTest'],
                'Acme\Test\FooTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'all classes in the namespace' . $titleSuffix => [
                ['Acme\Test\*'],
                'Acme\Test\FooTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'pattern of a class with method' . $titleSuffix => [
                ['Acme\Test\*::test_it_can_create_instance'],
                'Acme\Test\FooTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'pattern of a method' . $titleSuffix => [
                ['Acme\Test\FooTest::test_i?_can_create_instanc?'],
                'Acme\Test\FooTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];
        }

        yield 'full class with method and line number' => [
            ['Acme\FooTest::test_it_can_create_instance::50'],
            'Acme\FooTest',
            'test_it_can_create_instance',
            50,
        ];
    }

    public function nonIgnoredValuesProvider(): Generator
    {
        foreach ([null, 50] as $lineNumber) {
            $titleSuffix = null === $lineNumber ? '' : ' with line number #' . $lineNumber;

            yield 'full class with non-matching class' . $titleSuffix => [
                ['Acme\FooTest'],
                'Acme\BarTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'full class with non-matching case of the class' . $titleSuffix => [
                ['Acme\FooTest'],
                'AcmE\FoOTesT',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'full class with method with non-matching class' . $titleSuffix => [
                ['Acme\FooTest::test_it_can_create_instance'],
                'Acme\BarTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'full class with method with non-matching method' . $titleSuffix => [
                ['Acme\FooTest::test_it_can_create_instance'],
                'Acme\FooTest',
                'test_it_is_another_method',
                $lineNumber,
            ];

            yield 'full class with method with non-matching case of method' . $titleSuffix => [
                ['Acme\FooTest::test_it_can_create_instance'],
                'Acme\FooTest',
                'test_It_Can_Create_Instance',
                $lineNumber,
            ];

            yield 'non-matching pattern of a class' . $titleSuffix => [
                ['Acme\*\FooTest'],
                'Acme\FooTest',
                'test_it_can_create_instance',
                $lineNumber,
            ];

            yield 'pattern of a class with non-matching method' . $titleSuffix => [
                ['Acme\Test\*::test_it_can_create_instance'],
                'Acme\Test\FooTest',
                'test_it_is_another_method',
                $lineNumber,
            ];

            yield 'non-matching pattern of a method' . $titleSuffix => [
                ['Acme\Test\FooTest::test_i?_can_create_instanc?'],
                'Acme\Test\FooTest',
                'test_it_is_another_method',
                $lineNumber,
            ];
        }

        yield 'full class with method and non-matching line number' => [
            ['Acme\FooTest::test_it_can_create_instance::50'],
            'Acme\FooTest',
            'test_it_can_create_instance',
            70,
        ];
    }
}
