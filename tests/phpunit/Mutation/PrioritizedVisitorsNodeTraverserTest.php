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

namespace Infection\Tests\Mutation;

use DomainException;
use Generator;
use Infection\Mutation\PrioritizedVisitorsNodeTraverser;
use Infection\Tests\Fixtures\Mutation\FakeNodeTraverser;
use Infection\Tests\Fixtures\PhpParser\FakeVisitor;
use Infection\Tests\Fixtures\PhpParser\RecordedVisitor;
use InvalidArgumentException;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PHPUnit\Framework\TestCase;

final class PrioritizedVisitorsNodeTraverserTest extends TestCase
{
    /**
     * @var PrioritizedVisitorsNodeTraverser
     */
    private $traverser;

    protected function setUp(): void
    {
        $this->traverser = new PrioritizedVisitorsNodeTraverser(new FakeNodeTraverser());
    }

    public function test_it_cannot_add_visitors_without_a_priority(): void
    {
        try {
            $this->traverser->addVisitor(new FakeVisitor());

            $this->fail('Expected an exception to be thrown.');
        } catch (DomainException $exception) {
            $this->assertSame(
                'Add a non-prioritized visitor is not supported.',
                $exception->getMessage()
            );
        }
    }

    /**
     * @dataProvider visitorsWithPriorityProvider
     *
     * @param array<int, NodeVisitor> $expectedVisitors
     */
    public function test_it_can_add_visitors_which_will_be_sorted_by_priorities(
        array $visitorsWithPriority,
        array $expectedVisitors
    ): void {
        foreach ($visitorsWithPriority as [$visitor, $priority]) {
            /*
             * @var NodeVisitor $visitor
             * @var int         $priority
             */
            $this->traverser->addPrioritizedVisitor($visitor, $priority);
        }

        $this->assertSame($expectedVisitors, $this->traverser->getVisitors());
    }

    public function test_it_cannot_add_two_visitors_with_different_priorities(): void
    {
        $priority = 0;
        $visitor = new FakeVisitor();

        $this->traverser->addPrioritizedVisitor($visitor, $priority);

        $expectedErrorMessage = sprintf(
            'The priority "%s" is already used for the visitor "%s". Please use a different one',
            $priority,
            FakeVisitor::class
        );

        try {
            // Same visitor registered twice
            $this->traverser->addPrioritizedVisitor($visitor, $priority);

            $this->fail('Expected an exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame($expectedErrorMessage, $exception->getMessage());
        }

        try {
            // Different visitor
            $this->traverser->addPrioritizedVisitor(new FakeVisitor(), $priority);

            $this->fail('Expected an exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame($expectedErrorMessage, $exception->getMessage());
        }
    }

    public function test_it_cannot_remove_visitors(): void
    {
        try {
            $this->traverser->removeVisitor(new FakeVisitor());

            $this->fail('Expected an exception to be thrown.');
        } catch (DomainException $exception) {
            $this->assertSame(
                'Removing a visitor is not supported.',
                $exception->getMessage()
            );
        }
    }

    public function test_it_traverses_nodes_with_the_sorted_visitors(): void
    {
        $records = [];

        $visitor0 = new RecordedVisitor($records);
        $visitor1 = new RecordedVisitor($records);

        $traverser0 = new PrioritizedVisitorsNodeTraverser(new NodeTraverser());

        $traverser0->addPrioritizedVisitor($visitor0, 0);
        $traverser0->addPrioritizedVisitor($visitor1, 1);

        $traverser1 = new PrioritizedVisitorsNodeTraverser(new NodeTraverser());

        $traverser1->addPrioritizedVisitor($visitor0, 1);
        $traverser1->addPrioritizedVisitor($visitor1, 0);

        $traverser0->traverse([]);

        $this->assertSame([$visitor1, $visitor0], $records);

        $records = [];

        $traverser1->traverse([]);

        $this->assertSame([$visitor0, $visitor1], $records);
    }

    public function visitorsWithPriorityProvider(): Generator
    {
        yield 'empty' => [
            [],
            [],
        ];

        $visitor0 = new FakeVisitor();
        $visitor1 = new FakeVisitor();
        $visitor2 = new FakeVisitor();

        $priority0 = -10;
        $priority1 = 0;
        $priority2 = 10;

        $expectedVisitors = [
            $priority2 => $visitor2,
            $priority1 => $visitor1,
            $priority0 => $visitor0,
        ];

        // With the given fixtures above and while keeping the same visitor priority pair, then
        // for each possible permutations of the possible order of those pairs should yiel an
        // identical result.
        yield 'permutation 0' => [
            [
                [$visitor0, $priority0],
                [$visitor1, $priority1],
                [$visitor2, $priority2],
            ],
            $expectedVisitors,
        ];

        yield 'permutation 1' => [
            [
                [$visitor0, $priority0],
                [$visitor2, $priority2],
                [$visitor1, $priority1],
            ],
            $expectedVisitors,
        ];

        yield 'permutation 2' => [
            [
                [$visitor1, $priority1],
                [$visitor0, $priority0],
                [$visitor2, $priority2],
            ],
            $expectedVisitors,
        ];

        yield 'permutation 3' => [
            [
                [$visitor2, $priority2],
                [$visitor0, $priority0],
                [$visitor1, $priority1],
            ],
            $expectedVisitors,
        ];

        yield 'permutation 4' => [
            [
                [$visitor1, $priority1],
                [$visitor2, $priority2],
                [$visitor0, $priority0],
            ],
            $expectedVisitors,
        ];

        yield 'permutation 5' => [
            [
                [$visitor2, $priority2],
                [$visitor1, $priority1],
                [$visitor0, $priority0],
            ],
            $expectedVisitors,
        ];
    }
}
