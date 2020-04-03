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

use Infection\Mutation\Mutation;
use Infection\Mutator\NodeMutationGenerator;
use Infection\PhpParser\Visitor\MutationCollectorVisitor;
use function iterator_to_array;

/**
 * @group integration
 */
final class MutationCollectorVisitorTest extends BaseVisitorTest
{
    private const CODE = <<<'PHP'
<?php

class Foo {}

PHP;

    public function test_it_collects_the_generated_mutations(): void
    {
        $mutation0 = $this->createMock(Mutation::class);
        $mutation1 = $this->createMock(Mutation::class);
        $mutation2 = $this->createMock(Mutation::class);
        $mutation3 = $this->createMock(Mutation::class);
        $mutation4 = $this->createMock(Mutation::class);

        $nodeMutationGeneratorMock = $this->createMock(NodeMutationGenerator::class);
        $nodeMutationGeneratorMock
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                [$mutation0, $mutation1],
                [$mutation2],
                [$mutation3, $mutation4]
            )
        ;

        $visitor = new MutationCollectorVisitor($nodeMutationGeneratorMock);

        $this->traverse(
            $this->parseCode(self::CODE),
            [$visitor]
        );

        $this->assertSame(
            [
                $mutation0,
                $mutation1,
                $mutation2,
                // We only expect 2 calls here – because of the code parsed: hence even if the
                // generator can produce _more_ mutations, we only call it as many times as we need
                // it, not as many times it can create mutations
            ],
            iterator_to_array($visitor->getMutations(), false)
        );
    }

    public function test_it_resets_its_state_between_two_traverse(): void
    {
        $mutation0 = $this->createMock(Mutation::class);
        $mutation1 = $this->createMock(Mutation::class);
        $mutation2 = $this->createMock(Mutation::class);
        $mutation3 = $this->createMock(Mutation::class);
        $mutation4 = $this->createMock(Mutation::class);

        $nodeMutationGeneratorMock = $this->createMock(NodeMutationGenerator::class);
        $nodeMutationGeneratorMock
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                [$mutation0, $mutation1],
                [$mutation2],
                [$mutation3, $mutation4],
                []
            )
        ;

        $visitor = new MutationCollectorVisitor($nodeMutationGeneratorMock);

        $this->traverse(
            $this->parseCode(self::CODE),
            [$visitor]
        );

        // Sanity check
        $this->assertSame(
            [
                $mutation0,
                $mutation1,
                $mutation2,
            ],
            iterator_to_array($visitor->getMutations(), false)
        );

        $this->traverse(
            $this->parseCode(self::CODE),
            [$visitor]
        );

        $this->assertSame(
            [
                $mutation3,
                $mutation4,
            ],
            iterator_to_array($visitor->getMutations(), false)
        );
    }
}
