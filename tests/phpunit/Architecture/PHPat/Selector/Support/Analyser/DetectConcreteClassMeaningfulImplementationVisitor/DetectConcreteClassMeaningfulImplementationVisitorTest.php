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

namespace Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor;

use Infection\Testing\SingletonContainer;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\Analyser;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\ConstructorWithBody;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\EmptyClass;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\EmptyMethod;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\MethodWithExecutableExpression;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\NonNullObjectReturningSimpleExpression;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\NullObjectReturningArray;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\NullObjectReturningConstant;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\NullObjectReturningNull;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\NullObjectReturningScalar;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\OnlyClassConstants;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\OnlyProperties;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\OnlyTraitUse;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\PromotedPropertyConstructor;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\UnexpectedCallMethod;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\UnexpectedCallUsingFactory;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\UnexpectedCallWithDifferentException;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor\Fixture\UnexpectedCallWithDifferentMessage;
use Infection\Tests\TestFramework\Tracing\Trace\FakeTrace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DetectConcreteClassMeaningfulImplementationVisitor::class)]
final class DetectConcreteClassMeaningfulImplementationVisitorTest extends SelectorTestCase
{
    private Analyser $analyser;

    protected function setUp(): void
    {
        $container = SingletonContainer::getContainer();

        $this->analyser = new Analyser(
            $container->getParser(),
            $container->getFileSystem(),
        );
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_detects_whether_a_concrete_class_has_meaningful_implementation(
        string $className,
        bool $expected,
    ): void {
        $classReflection = $this->createClassReflection($className);

        $actual = !$this->analyser
            ->analyse($classReflection)
            ->hasTrivialImplementation;

        $this->assertSame($expected, $actual);
    }

    public static function classProvider(): iterable
    {
        yield 'empty class' => [
            EmptyClass::class,
            false,
        ];

        yield 'class constants only' => [
            OnlyClassConstants::class,
            false,
        ];

        yield 'properties only' => [
            OnlyProperties::class,
            false,
        ];

        yield 'trait use only' => [
            OnlyTraitUse::class,
            false,
        ];

        yield 'empty method' => [
            EmptyMethod::class,
            false,
        ];

        yield 'promoted property constructor' => [
            PromotedPropertyConstructor::class,
            false,
        ];

        yield 'null object returning null' => [
            NullObjectReturningNull::class,
            false,
        ];

        yield 'null object returning array' => [
            NullObjectReturningArray::class,
            false,
        ];

        yield 'null object returning scalar' => [
            NullObjectReturningScalar::class,
            false,
        ];

        yield 'null object returning constant' => [
            NullObjectReturningConstant::class,
            false,
        ];

        yield 'unexpected call method' => [
            UnexpectedCallMethod::class,
            false,
        ];

        yield 'method with executable expression' => [
            MethodWithExecutableExpression::class,
            true,
        ];

        yield 'constructor with body' => [
            ConstructorWithBody::class,
            true,
        ];

        yield 'non-null object returning simple expression' => [
            NonNullObjectReturningSimpleExpression::class,
            false,
        ];

        yield 'unexpected call with different exception' => [
            UnexpectedCallWithDifferentException::class,
            false,
        ];

        yield 'unexpected call with different message' => [
            UnexpectedCallWithDifferentMessage::class,
            false,
        ];

        yield 'unexpected call using factory' => [
            UnexpectedCallUsingFactory::class,
            false,
        ];

        yield 'fake trace' => [
            FakeTrace::class,
            false,
        ];
    }
}
