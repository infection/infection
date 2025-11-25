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

namespace Infection\Tests\Framework;

use Closure;
use Infection\Framework\ClassName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassName::class)]
final class ClassNameTest extends TestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classNameProvider')]
    public function test_it_can_get_a_class_short_name(
        string $className,
        string $expectedShortName,
    ): void {
        $actual = ClassName::getShortClassName($className);

        $this->assertSame($expectedShortName, $actual);
    }

    public static function classNameProvider(): iterable
    {
        yield 'nominal' => [
            ClassName::class,
            'ClassName',
        ];

        yield 'UTF-8' => [
            'Webmozarts\ClassName\Ütf8',
            'Ütf8',
        ];

        yield 'emoji' => [
            'Webmozarts\ClassName\Special😋Class',
            'Special😋Class',
        ];

        yield 'root namespace' => [
            Closure::class,
            'Closure',
        ];
    }

    public function test_it_can_give_the_source_class_name_for_a_test_case_class(): void
    {
        $this->assertSame(
            'Infection\Acme\Foo',
            ClassName::getCanonicalSourceClassName('Infection\Tests\Acme\FooTest'),
        );
    }

    public function test_it_can_give_the_source_class_name_for_a_source_class(): void
    {
        $this->assertSame(
            'Infection\Acme\Foo',
            ClassName::getCanonicalSourceClassName('Infection\Acme\Foo'),
        );
    }

    public function test_it_can_give_the_test_case_class_name_for_a_source_class(): void
    {
        $this->assertSame(
            'Infection\Tests\Acme\FooTest',
            ClassName::getCanonicalTestClassName('Infection\Acme\Foo'),
        );
    }

    public function test_it_can_give_the_test_case_class_name_for_a_test_source_class(): void
    {
        $this->assertSame(
            'Infection\Tests\Acme\FooTest',
            ClassName::getCanonicalTestClassName('Infection\Tests\Acme\Foo'),
        );
    }
}
