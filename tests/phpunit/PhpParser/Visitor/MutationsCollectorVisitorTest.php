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

namespace Infection\Tests\PhpParser\Visitor;

use Infection\Mutator\NodeMutationGenerator;
use Infection\PhpParser\Visitor\MutationsCollectorVisitor;
use PhpParser\Node;
use Prophecy\Argument;
use Prophecy\Argument\Token\TokenInterface;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

/**
 * @group integration Requires some I/O operations
 */
final class MutationsCollectorVisitorTest extends BaseVisitorTest
{
    private const CODE = <<<'PHP'
<?php

class Foo {}

PHP;

    public function test_it_collects_the_generated_mutations(): void
    {
        $nodeMutationGeneratorMock = $this->createMock(NodeMutationGenerator::class);
        $nodeMutationGeneratorMock
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturn([$mutation = new stdClass()])
        ;

        $visitor = new MutationsCollectorVisitor($nodeMutationGeneratorMock);

        $this->traverse(
            $this->parseCode(self::CODE),
            [$visitor]
        );

        $this->assertSame([$mutation, $mutation], $visitor->getMutations());
    }

    public function test_it_resets_its_state_between_two_traverse(): void
    {
        /** @var ObjectProphecy|NodeMutationGenerator $nodeMutationGeneratorProphecy */
        $nodeMutationGeneratorProphecy = $this->prophesize(NodeMutationGenerator::class);

        $node0 = $this->createMock(Node::class);
        $node1 = $this->createMock(Node::class);
        $node2 = $this->createMock(Node::class);
        $node3 = $this->createMock(Node::class);

        $nodeMutationGeneratorProphecy
            ->generate(self::createExactArgument($node0))
            ->willReturn([$mutation0 = new stdClass()])
        ;
        $nodeMutationGeneratorProphecy
            ->generate(self::createExactArgument($node1))
            ->willReturn([])
        ;
        $nodeMutationGeneratorProphecy
            ->generate(self::createExactArgument($node2))
            ->willReturn([$mutation2 = new stdClass()])
        ;
        $nodeMutationGeneratorProphecy
            ->generate(self::createExactArgument($node3))
            ->willReturn([$mutation3 = new stdClass()])
        ;

        $visitor = new MutationsCollectorVisitor($nodeMutationGeneratorProphecy->reveal());

        $this->traverse(
            [$node0, $node1],
            [$visitor]
        );

        $this->assertSame([$mutation0], $visitor->getMutations());

        $this->traverse(
            [$node2, $node3],
            [$visitor]
        );

        $this->assertSame([$mutation2, $mutation3], $visitor->getMutations());
    }

    private static function createExactArgument(object $value): TokenInterface
    {
        return Argument::that(static function ($arg) use ($value): bool {
            return $arg === $value;
        });
    }
}
