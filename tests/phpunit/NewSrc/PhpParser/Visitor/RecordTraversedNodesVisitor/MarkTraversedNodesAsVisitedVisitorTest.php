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

namespace Infection\Tests\NewSrc\PhpParser\Visitor\RecordTraversedNodesVisitor;

use Infection\Tests\NewSrc\PhpParser\Visitor\VisitorTestCase;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MarkTraversedNodesAsVisitedVisitor::class)]
final class MarkTraversedNodesAsVisitedVisitorTest extends VisitorTestCase
{
    public function test_it_records_the_traversed_nodes(): void
    {
        $nodes = $this->parser->parse(
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                );

                PHP,
        );

        /** @var Node\Stmt\Namespace_ $namespace */
        $namespace = $nodes[0];
        $namespaceName = $namespace->name;

        /** @var Node\Stmt\Expression $assignmentExpression */
        $assignmentExpression = $namespace->stmts[0];
        /** @var Node\Expr\Assign $assignmentStmt */
        $assignmentStmt = $assignmentExpression->expr;
        /** @var Node\Expr\Variable $variable */
        $variable = $assignmentStmt->var;
        /** @var Node\Expr\New_ $newStmt */
        $newStmt = $assignmentStmt->expr;
        /** @var Node\Name $newClassName */
        $newClassName = $newStmt->class;
        /** @var Node\Arg $newStmtArg */
        $newStmtArg = $newStmt->args[0];

        $expected = [
            ['beforeTraverse', [$nodes]],
            ['enterNode', [$namespace]],
            ['enterNode', [$namespaceName]],
            ['leaveNode', [$namespaceName]],
            ['enterNode', [$assignmentExpression]],
            ['enterNode', [$assignmentStmt]],
            ['enterNode', [$variable]],
            ['leaveNode', [$variable]],
            ['enterNode', [$newStmt]],
            ['enterNode', [$newClassName]],
            ['leaveNode', [$newClassName]],
            ['leaveNode', [$newStmt]],
            ['leaveNode', [$assignmentStmt]],
            ['leaveNode', [$assignmentExpression]],
            ['leaveNode', [$namespace]],
            ['afterTraverse', [$nodes]],
        ];

        $recorder = new MarkTraversedNodesAsVisitedVisitor();

        $stopAtFirstArgVisitor = new class($newStmt) extends NodeVisitorAbstract {
            public function __construct(private readonly Node $nodeToStopOn)
            {
            }

            public function enterNode(Node $node)
            {
                if ($node === $this->nodeToStopOn) {
                    return NodeVisitorAbstract::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
            }
        };

        $traverser = new NodeTraverser(
            $stopAtFirstArgVisitor,
            $recorder,
        );

        $traverser->traverse($nodes);

        $this->assertSame(
            '',
            $this->dumper->dump(
                $recorder->fetch(),
            ),
        );
    }
}
