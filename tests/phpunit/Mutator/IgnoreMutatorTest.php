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

use DomainException;
use Generator;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Mutator;
use Infection\Reflection\CoreInfectionReflectionClass;
use Infection\Visitor\ReflectionVisitor;
use function iterator_to_array;
use PhpParser\Node;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class IgnoreMutatorTest extends TestCase
{
    /**
     * @var MockObject|Mutator
     */
    private $mutatorMock;

    /**
     * @var MockObject|Node
     */
    private $nodeMock;

    protected function setUp(): void
    {
        $this->mutatorMock = $this->createMock(Mutator::class);
        $this->nodeMock = $this->createMock(Node::class);
    }

    public function test_it_cannot_give_a_definition(): void
    {
        try {
            IgnoreMutator::getDefinition();

            $this->fail();
        } catch (DomainException $exception) {
            $this->assertSame(
                'The class "Infection\Mutator\IgnoreMutator" does not have a definition',
                $exception->getMessage()
            );
        }
    }

    public function test_it_should_not_mutate_node_if_its_decorated_mutator_cannot(): void
    {
        $ignoreMutator = new IgnoreMutator(new IgnoreConfig([]), $this->mutatorMock);

        $this->mutatorMock
            ->expects($this->once())
            ->method('canMutate')
            ->with($this->nodeMock)
            ->willReturn(false)
        ;

        $mutate = $ignoreMutator->canMutate($this->nodeMock);

        $this->assertFalse($mutate);
    }

    public function test_it_should_mutate_node_if_its_decorated_mutator_can_and_no_reflection_class_could_be_found_for_the_node(): void
    {
        $ignoreMutator = new IgnoreMutator(new IgnoreConfig([]), $this->mutatorMock);

        $this->mutatorMock
            ->expects($this->once())
            ->method('canMutate')
            ->with($this->nodeMock)
            ->willReturn(true)
        ;

        $this->nodeMock
            ->expects($this->once())
            ->method('getAttribute')
            ->with(ReflectionVisitor::REFLECTION_CLASS_KEY, false)
            ->willReturn(false)
        ;

        $mutate = $ignoreMutator->canMutate($this->nodeMock);

        $this->assertTrue($mutate);
    }

    public function test_it_should_not_mutate_node_if_its_decorated_mutator_can_and_a_reflection_class_could_be_found_for_the_node_and_the_node_is_ignored(): void
    {
        $this->mutatorMock
            ->expects($this->once())
            ->method('canMutate')
            ->with($this->nodeMock)
            ->willReturn(true)
        ;

        $this->nodeMock
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(
                [ReflectionVisitor::REFLECTION_CLASS_KEY, false],
                [ReflectionVisitor::FUNCTION_NAME, '']
            )
            ->willReturnOnConsecutiveCalls(
                new CoreInfectionReflectionClass(new ReflectionClass(self::class)),
                'foo'
            )
        ;

        $this->nodeMock
            ->expects($this->once())
            ->method('getLine')
            ->willReturn(10)
        ;

        $ignoreConfigMock = $this->createMock(IgnoreConfig::class);

        $ignoreConfigMock
            ->expects($this->once())
            ->method('isIgnored')
            ->with(self::class, 'foo', 10)
            ->willReturn(true)
        ;

        $ignoreMutator = new IgnoreMutator($ignoreConfigMock, $this->mutatorMock);

        $mutate = $ignoreMutator->canMutate($this->nodeMock);

        $this->assertFalse($mutate);
    }

    public function test_it_mutates_the_node_via_its_decorated_mutator(): void
    {
        $ignoreMutator = new IgnoreMutator(new IgnoreConfig([]), $this->mutatorMock);

        $mutatedNodeMock = $this->createMock(Node::class);

        $this->mutatorMock
            ->expects($this->once())
            ->method('mutate')
            ->with($this->nodeMock)
            ->willReturnCallback(static function () use ($mutatedNodeMock): Generator {
                yield $mutatedNodeMock;
            })
        ;

        $mutatedNode = $ignoreMutator->mutate($this->nodeMock);

        $this->assertSame([$mutatedNodeMock], iterator_to_array($mutatedNode));
    }

    public function test_it_exposes_its_decorated_mutator_name(): void
    {
        $ignoreMutator = new IgnoreMutator(new IgnoreConfig([]), new Plus());

        $this->assertSame(
            MutatorName::getName(Plus::class),
            $ignoreMutator->getName()
        );
    }
}
