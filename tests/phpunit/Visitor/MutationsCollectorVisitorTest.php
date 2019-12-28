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

namespace Infection\Tests\Visitor;

use Infection\Mutator\NodeMutationGenerator;
use Infection\Visitor\MutationsCollectorVisitor;
use PhpParser\Node;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

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
            ->willReturn($mutations = [new stdClass()])
        ;

        $visitor = new MutationsCollectorVisitor($nodeMutationGeneratorMock);

        $this->traverse(
            $this->parseCode(self::CODE),
            [$visitor]
        );

        $this->assertSame($mutations, $visitor->getMutations());
    }

    public function test_it_resets_its_state_between_two_traverse(): void
    {
        /** @var ObjectProphecy|NodeMutationGenerator $nodeMutationGeneratorProphecy */
        $nodeMutationGeneratorProphecy = $this->prophesize(NodeMutationGenerator::class);

        $node0 = $this->createMock(Node::class);
        $node1 = $this->createMock(Node::class);

        $nodeMutationGeneratorProphecy
            ->generate(Argument::that(static function ($arg) use ($node0): bool {
                return $arg === $node0;
            }))
            ->willReturn($mutations0 = [new stdClass()])
        ;
        $nodeMutationGeneratorProphecy
            ->generate(Argument::that(static function ($arg) use ($node1): bool {
                return $arg === $node1;
            }))
            ->willReturn($mutations1 = [new stdClass()])
        ;

        $visitor = new MutationsCollectorVisitor($nodeMutationGeneratorProphecy->reveal());

        // Sanity check
        $this->assertNotSame($mutations1, $mutations0);

        $this->traverse(
            [$node0],
            [$visitor]
        );

        $this->assertSame($mutations0, $visitor->getMutations());

        $this->traverse(
            [$node1],
            [$visitor]
        );

        $this->assertSame($mutations1, $visitor->getMutations());
    }
}
