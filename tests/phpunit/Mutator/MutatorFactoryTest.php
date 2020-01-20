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

use function array_values;
use function count;
use function get_class;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\IdenticalEqual;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorFactory;
use Infection\Visitor\ReflectionVisitor;
use InvalidArgumentException;
use PhpParser\Node;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\sprintf;

final class MutatorFactoryTest extends TestCase
{
    /**
     * @var MutatorFactory
     */
    private $mutatorFactory;

    protected function setUp(): void
    {
        $this->mutatorFactory = new MutatorFactory();
    }

    public function test_it_creates_no_mutator_if_no_profile_or_mutator_is_passed(): void
    {
        $mutators = $this->mutatorFactory->create([]);

        $this->assertSame([], $mutators);
    }

    public function test_it_can_create_the_mutators_with_empty_settings(): void
    {
        $mutators = $this->mutatorFactory->create([
            Plus::class => [],
            IdenticalEqual::class => [],
        ]);

        $this->assertSameMutatorsByClass(
            [Plus::class, IdenticalEqual::class],
            $mutators
        );
    }

    public function test_it_can_create_a_mutator_with_the_given_settings(): void
    {
        // TODO: refactor this test to check the mutator configuration directly instead of relying
        // on the canMutate() API which ends up testing other stuff and is also more cumbersome
        // to employ.

        $mutators = $this->mutatorFactory->create([
            TrueValue::class => [
                'ignore' => ['A::B'],
            ],
        ]);

        $this->assertSameMutatorsByClass([TrueValue::class], $mutators);

        /** @var MockObject|ReflectionClass $reflectionMock */
        $reflectionMock = $this->createMock(ReflectionClass::class);
        $reflectionMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('A')
        ;

        $trueNode = $this->createBoolNode(
            'true',
            'B',
            $reflectionMock
        );

        $this->assertFalse(
            $mutators[MutatorName::getName(TrueValue::class)]->canMutate($trueNode)
        );
    }

    public function test_it_cannot_create_a_mutator_with_invalid_settings(): void
    {
        try {
            $this->mutatorFactory->create([Plus::class => false]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected settings of the mutator "Infection\Mutator\Arithmetic\Plus" to be an array. Got "boolean" instead',
                $exception->getMessage()
            );
        }
    }

    public function test_it_can_create_the_mutators_with_unknown_settings(): void
    {
        $mutators = $this->mutatorFactory->create([
            Plus::class => ['unknown' => 'dunno'],
        ]);

        $this->assertSameMutatorsByClass([Plus::class], $mutators);
    }

    public function test_it_cannot_create_an_unknown_mutator(): void
    {
        try {
            $this->mutatorFactory->create(['Unknwon\Mutator' => []]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Unknown mutator "Unknwon\Mutator"',
                $exception->getMessage()
            );
        }
    }

    private function createBoolNode(
        string $boolean,
        string $functionName,
        ReflectionClass $reflectionMock
    ): Node {
        return new Node\Expr\ConstFetch(
            new Node\Name($boolean),
            [
                ReflectionVisitor::REFLECTION_CLASS_KEY => $reflectionMock,
                ReflectionVisitor::FUNCTION_NAME => $functionName,
            ]
        );
    }

    /**
     * @param string[]               $expectedMutatorClassNames
     * @param array<string, Mutator> $actualMutators
     */
    private function assertSameMutatorsByClass(
        array $expectedMutatorClassNames,
        array $actualMutators
    ): void {
        $this->assertCount(count($expectedMutatorClassNames), $actualMutators);

        $decoratedMutatorReflection = (new ReflectionClass(IgnoreMutator::class))->getProperty('mutator');
        $decoratedMutatorReflection->setAccessible(true);

        foreach (array_values($actualMutators) as $index => $mutator) {
            $this->assertInstanceOf(IgnoreMutator::class, $mutator);

            $expectedMutatorClass = $expectedMutatorClassNames[$index];
            $actualMutatorClass = get_class($decoratedMutatorReflection->getValue($mutator));

            $this->assertSame(
                $expectedMutatorClass,
                $actualMutatorClass,
                sprintf(
                    'Expected the %d-th mutator to be an instance of "%s". Got "%s"',
                    $index,
                    $expectedMutatorClass,
                    $actualMutatorClass
                )
            );
        }
    }
}
