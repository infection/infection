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

namespace Infection\Tests\Architecture\PHPat\Selector\Support;

use Infection\CannotBeInstantiated;
use Infection\Command\BaseCommand;
use Infection\Engine;
use Infection\Mutator\Mutator;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use Infection\Tests\Architecture\PHPat\Selector\Support\ClassReflectionPredicatesTest\Fixtures\ChildWithInheritedMembers;
use Infection\Tests\Architecture\PHPat\Selector\Support\ClassReflectionPredicatesTest\Fixtures\ParentWithDeclaredMembers;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use ReflectionProperty;

#[CoversClass(ClassReflectionPredicates::class)]
final class ClassReflectionPredicatesTest extends SelectorTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_detects_concrete_classes(
        string $className,
        bool $expected,
    ): void {
        $classReflection = $this->createClassReflection($className);

        $actual = ClassReflectionPredicates::isConcreteClass($classReflection);

        $this->assertSame($expected, $actual);
    }

    public static function classProvider(): iterable
    {
        yield 'concrete class' => [
            Engine::class,
            true,
        ];

        yield 'abstract class' => [
            BaseCommand::class,
            false,
        ];

        yield 'interface' => [
            Mutator::class,
            false,
        ];

        yield 'trait' => [
            CannotBeInstantiated::class,
            false,
        ];
    }

    public function test_it_detects_inherited_methods(): void
    {
        $childClassReflection = $this->createClassReflection(ChildWithInheritedMembers::class);
        $parentClassReflection = $this->createClassReflection(ParentWithDeclaredMembers::class);
        $methodReflection = new ReflectionMethod(ChildWithInheritedMembers::class, 'execute');

        $this->assertTrue(
            ClassReflectionPredicates::isInheritedMethod(
                $methodReflection,
                $childClassReflection,
            ),
        );
        $this->assertFalse(
            ClassReflectionPredicates::isInheritedMethod(
                $methodReflection,
                $parentClassReflection,
            ),
        );
    }

    public function test_it_detects_inherited_properties(): void
    {
        $childClassReflection = $this->createClassReflection(ChildWithInheritedMembers::class);
        $parentClassReflection = $this->createClassReflection(ParentWithDeclaredMembers::class);
        $propertyReflection = new ReflectionProperty(ChildWithInheritedMembers::class, 'value');

        $this->assertTrue(
            ClassReflectionPredicates::isInheritedProperty(
                $propertyReflection,
                $childClassReflection,
            ),
        );
        $this->assertFalse(
            ClassReflectionPredicates::isInheritedProperty(
                $propertyReflection,
                $parentClassReflection,
            ),
        );
    }
}
