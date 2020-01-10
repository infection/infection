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

use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Mutator;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Visitor\ReflectionVisitor;
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

    public function test_it_should_not_mutate_node_if_its_decorated_mutator_cannot(): void
    {
        $ignoreMutator = new IgnoreMutator(new MutatorConfig([]), $this->mutatorMock);

        $this->mutatorMock
            ->expects($this->once())
            ->method('canMutate')
            ->with($this->nodeMock)
            ->willReturn(false)
        ;

        $mutate = $ignoreMutator->shouldMutate($this->nodeMock);

        $this->assertFalse($mutate);
    }

    public function test_it_should_mutate_node_if_its_decorated_mutator_can_and_no_reflection_class_could_be_found_for_the_node(): void
    {
        $ignoreMutator = new IgnoreMutator(new MutatorConfig([]), $this->mutatorMock);

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

        $mutate = $ignoreMutator->shouldMutate($this->nodeMock);

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
                new ReflectionClass(self::class),
                'foo'
            )
        ;

        $this->nodeMock
            ->expects($this->once())
            ->method('getLine')
            ->willReturn(10)
        ;

        $configMock = $this->createMock(MutatorConfig::class);

        $configMock
            ->expects($this->once())
            ->method('isIgnored')
            ->with(self::class, 'foo', 10)
            ->willReturn(true)
        ;

        $ignoreMutator = new IgnoreMutator($configMock, $this->mutatorMock);

        $mutate = $ignoreMutator->shouldMutate($this->nodeMock);

        $this->assertFalse($mutate);
    }

    public function test_it_mutates_the_node_via_its_decorated_mutator(): void
    {
        $ignoreMutator = new IgnoreMutator(new MutatorConfig([]), $this->mutatorMock);

        $mutatedNodeMock = $this->createMock(Node::class);

        $this->mutatorMock
            ->expects($this->once())
            ->method('mutate')
            ->with($this->nodeMock)
            ->willReturn($mutatedNodeMock)
        ;

        $mutatedNode = $ignoreMutator->mutate($this->nodeMock);

        $this->assertSame($mutatedNodeMock, $mutatedNode);
    }

    public function test_it_exposes_its_decorated_mutator(): void
    {
        $ignoreMutator = new IgnoreMutator(new MutatorConfig([]), $this->mutatorMock);

        $this->assertSame($this->mutatorMock, $ignoreMutator->getMutator());
    }
}
