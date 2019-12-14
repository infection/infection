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

use;
use Infection\Mutation\PriorityNodeTraverser;
use InvalidArgumentException;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPUnit\Framework\TestCase;

final class PriorityNodeTraverserTest extends TestCase
{
    public function test_it_sorts_visitors_by_priorities(): void
    {
        $traverser = new PriorityNodeTraverser();

        $callOrder = [];

        $traverser->addVisitor($this->createVisitor($callOrder, 20), 20);
        $traverser->addVisitor($this->createVisitor($callOrder, 30), 30);
        $traverser->addVisitor($this->createVisitor($callOrder, 5), 5);
        $traverser->addVisitor($this->createVisitor($callOrder, 10), 10);

        $traverser->traverse([]);

        $this->assertSame([30, 20, 10, 5], $callOrder);
    }

    public function test_it_does_not_allow_duplicater_priorities(): void
    {
        $traverser = new PriorityNodeTraverser();

        $callOrder = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Priority 20 is already used');

        $traverser->addVisitor($this->createVisitor($callOrder, 20), 20);
        $traverser->addVisitor($this->createVisitor($callOrder, 20), 20);
    }

    private function createVisitor(array &$callOrder, int $priority): NodeVisitor
    {
        return new class($callOrder, $priority) extends NodeVisitorAbstract {
            public $callOrder;
            public $priority;

            public function __construct(array &$callOrder, int $priority)
            {
                $this->callOrder = &$callOrder;
                $this->priority = $priority;
            }

            public function beforeTraverse(array $nodes): void
            {
                $this->callOrder[] = $this->priority;
            }
        };
    }
}
