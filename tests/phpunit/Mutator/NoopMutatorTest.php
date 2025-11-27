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
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Mutator;
use Infection\Mutator\NoopMutator;
use Infection\Testing\MutatorName;
use function iterator_to_array;
use PhpParser\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopMutator::class)]
final class NoopMutatorTest extends TestCase
{
    private MockObject&Mutator $mutatorMock;

    private MockObject&Node $nodeMock;

    protected function setUp(): void
    {
        $this->mutatorMock = $this->createMock(Mutator::class);
        $this->nodeMock = $this->createMock(Node::class);
    }

    public function test_it_cannot_give_a_definition(): void
    {
        try {
            NoopMutator::getDefinition();

            $this->fail();
        } catch (DomainException $exception) {
            $this->assertSame(
                'The class "Infection\Mutator\NoopMutator" does not have a definition',
                $exception->getMessage(),
            );
        }
    }

    public function test_it_should_not_mutate_node_if_its_decorated_mutator_cannot(): void
    {
        $ignoreMutator = new NoopMutator($this->mutatorMock);

        $this->mutatorMock
            ->expects($this->once())
            ->method('canMutate')
            ->with($this->nodeMock)
            ->willReturn(false)
        ;

        $mutate = $ignoreMutator->canMutate($this->nodeMock);

        $this->assertFalse($mutate);
    }

    public function test_it_should_mutate_node_if_its_decorated_mutator_can(): void
    {
        $ignoreMutator = new NoopMutator($this->mutatorMock);

        $this->mutatorMock
            ->expects($this->once())
            ->method('canMutate')
            ->with($this->nodeMock)
            ->willReturn(true)
        ;

        $mutate = $ignoreMutator->canMutate($this->nodeMock);

        $this->assertTrue($mutate);
    }

    public function test_it_does_not_mutate_the_node(): void
    {
        $ignoreMutator = new NoopMutator($this->mutatorMock);

        $mutatedNode = $ignoreMutator->mutate($this->nodeMock);

        $this->assertSame([$this->nodeMock], iterator_to_array($mutatedNode));
    }

    public function test_it_exposes_its_decorated_mutator_name(): void
    {
        $ignoreMutator = new NoopMutator(new Plus());

        $this->assertSame(
            MutatorName::getName(Plus::class),
            $ignoreMutator->getName(),
        );
    }
}
