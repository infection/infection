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

use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use function array_diff;
use function array_values;
use function count;
use function get_class;
use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\IdenticalEqual;
use Infection\Mutator\Boolean\NotIdenticalNotEqual;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\ProfileList;
use Infection\Mutator\Util\Mutator;
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

        $this->assertCount(0, $mutators);
    }

    public function test_it_can_creates_the_mutators_for_a_given_profile(): void
    {
        $mutators = $this->mutatorFactory->create(['@boolean' => true]);

        $this->assertSameMutatorsByClass(ProfileList::BOOLEAN_PROFILE, $mutators);
    }

    public function test_it_can_creates_the_mutators_with_empty_settings_for_a_given_profile(): void
    {
        $mutators = $this->mutatorFactory->create(['@boolean' => []]);

        $this->assertSameMutatorsByClass(ProfileList::BOOLEAN_PROFILE, $mutators);
    }

    public function test_it_can_creates_the_profile_mutators_with_the_given_settings(): void
    {
        // TODO: refactor this test to check the mutator configuration directly instead of relying
        // on the shouldMutate() API which ends up testing other stuff and is also more cumbersome
        // to employ.

        $mutators = $this->mutatorFactory->create([
            '@default' => true,
            '@boolean' => [
                'ignore' => ['A::B'],
            ],
        ]);

        $this->assertSameMutatorsByClass(ProfileList::getDefaultProfileMutators(), $mutators);

        /** @var MockObject|ReflectionClass $reflectionMock */
        $reflectionMock = $this->createMock(ReflectionClass::class);
        $reflectionMock
            ->expects($this->exactly(3))
            ->method('getName')
            ->willReturn('A')
        ;

        $plusNode = $this->createPlusNode('B', $reflectionMock);
        $falseNode = $this->createBoolNode('false', 'B', $reflectionMock);
        $trueNode = $this->createBoolNode('true', 'B', $reflectionMock);

        $this->assertTrue($mutators[Plus::getName()]->shouldMutate($plusNode));
        $this->assertFalse($mutators[TrueValue::getName()]->shouldMutate($trueNode));
        $this->assertFalse($mutators[FalseValue::getName()]->shouldMutate($falseNode));
    }

    public function test_it_can_ignore_a_profile(): void
    {
        $mutators = $this->mutatorFactory->create(['@boolean' => false]);

        $this->assertCount(0, $mutators);
    }

    public function test_it_will_remove_the_mutators_from_the_ignored_profile_even_if_included_from_a_different_profile(): void
    {
        $mutators = $this->mutatorFactory->create([
             '@default' => true,
             '@boolean' => false,
        ]);

        $expectedMutators = array_values(array_diff(
            ProfileList::getDefaultProfileMutators(),
            ProfileList::BOOLEAN_PROFILE
        ));

        $this->assertSameMutatorsByClass($expectedMutators, $mutators);
    }

    public function test_it_will_not_remove_the_mutators_from_the_ignored_profile_if_its_mutators_are_included_after(): void
    {
        $mutators = $this->mutatorFactory->create([
            '@default' => false,
            '@boolean' => true,
        ]);

        $this->assertSameMutatorsByClass(ProfileList::BOOLEAN_PROFILE, $mutators);
    }

    public function test_it_can_create_mutators_from_their_names(): void
    {
        $mutators = $this->mutatorFactory->create([
            Plus::getName() => true,
            Minus::getName() => true,
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                Minus::class,
            ],
            $mutators
        );
    }

    public function test_it_can_create_mutators_with_empty_settings_from_their_names(): void
    {
        $mutators = $this->mutatorFactory->create([
            Plus::getName() => [],
            Minus::getName() => [],
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                Minus::class,
            ],
            $mutators
        );
    }

    public function test_it_can_create_a_mutator_with_the_given_settings(): void
    {
        // TODO: refactor this test to check the mutator configuration directly instead of relying
        // on the shouldMutate() API which ends up testing other stuff and is also more cumbersome
        // to employ.

        $mutators = $this->mutatorFactory->create([
            '@boolean' => true,
            TrueValue::getName() => [
                'ignore' => ['A::B'],
            ],
        ]);

        $this->assertSameMutatorsByClass(ProfileList::BOOLEAN_PROFILE, $mutators);

        /** @var MockObject|ReflectionClass $reflectionMock */
        $reflectionMock = $this->createMock(ReflectionClass::class);
        $reflectionMock
            ->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('A')
        ;

        $falseNode = $this->createBoolNode('false', 'B', $reflectionMock);
        $trueNode = $this->createBoolNode('true', 'B', $reflectionMock);

        $this->assertFalse($mutators[TrueValue::getName()]->shouldMutate($trueNode));
        $this->assertTrue($mutators[FalseValue::getName()]->shouldMutate($falseNode));
    }

    public function test_it_can_ignore_a_mutator(): void
    {
        $mutators = $this->mutatorFactory->create([Plus::getName() => false]);

        $this->assertCount(0, $mutators);
    }

    public function test_it_will_remove_the_ignored_mutators_if_they_were_included_previously(): void
    {
        $mutators = $this->mutatorFactory->create([
            '@equal' => true,
            IdenticalEqual::getName() => false,
        ]);

        $this->assertSameMutatorsByClass(
            [NotIdenticalNotEqual::class],
            $mutators
        );
    }

    public function test_it_will_not_remove_the_ignored_mutators_if_they_were_included_afterwards(): void
    {
        $mutators = $this->mutatorFactory->create([
            IdenticalEqual::getName() => false,
            '@equal' => true,
        ]);

        $this->assertSameMutatorsByClass(ProfileList::EQUAL_PROFILE, $mutators);
    }

    public function test_a_mutator_will_be_created_only_once_even_if_included_multiple_times(): void
    {
        $mutators = $this->mutatorFactory->create([
            IdenticalEqual::getName() => true,
            '@equal' => true,
            NotIdenticalNotEqual::getName() => true,
        ]);

        $this->assertSameMutatorsByClass(ProfileList::EQUAL_PROFILE, $mutators);
    }

    public function test_it_cannot_create_mutators_for_unknown_profiles(): void
    {
        try {
            $this->mutatorFactory->create(['@unknown-profile' => true]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'The profile or mutator "@unknown-profile" was not recognized.',
                $exception->getMessage()
            );
        }
    }

    public function test_it_cannot_create_an_unknown_mutator(): void
    {
        try {
            $this->mutatorFactory->create(['Unknwon\Mutator' => true]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'The profile or mutator "Unknwon\Mutator" was not recognized.',
                $exception->getMessage()
            );
        }
    }

    private function createPlusNode(string $functionName, ReflectionClass $reflectionMock): Node
    {
        return new Node\Expr\BinaryOp\Plus(
            new DNumber(1.23),
            new DNumber(1.23),
            [
                ReflectionVisitor::REFLECTION_CLASS_KEY => $reflectionMock,
                ReflectionVisitor::FUNCTION_NAME => $functionName,
            ]
        );
    }

    private function createBoolNode(string $boolean, string $functionName, ReflectionClass $reflectionMock): Node
    {
        return new ConstFetch(
            new Name($boolean),
            [
                ReflectionVisitor::REFLECTION_CLASS_KEY => $reflectionMock,
                ReflectionVisitor::FUNCTION_NAME => $functionName,
            ]
        );
    }

    /**
     * @param string[]               $expectedMutators
     * @param array<string, Mutator> $actualMutators
     */
    private function assertSameMutatorsByClass(array $expectedMutators, array $actualMutators): void
    {
        $this->assertCount(count($expectedMutators), $actualMutators);

        foreach (array_values($actualMutators) as $index => $mutator) {
            $expectedMutatorClass = $expectedMutators[$index];
            $actualMutatorClass = get_class($mutator);

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
