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

use Infection\PhpParser\Visitor\NextConnectingVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[CoversClass(NextConnectingVisitor::class)]
final class NextConnectingVisitorTest extends BaseVisitorTestCase
{
    public function test_it_connects_sequential_statements_with_next_attribute(): void
    {
        $code = <<<'PHP'
            <?php

            $a = 1;
            $b = 2;
            $c = 3;
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        $this->assertTrue($nodes[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($nodes[1], $nodes[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        $this->assertTrue($nodes[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($nodes[2], $nodes[1]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        $this->assertFalse($nodes[2]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }

    public function test_it_resets_previous_node_when_entering_function(): void
    {
        $code = <<<'PHP'
            <?php

            $a = 1;

            function test() {
                $b = 2;
                $c = 3;
            }

            $d = 4;
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        // $a = 1 has no next because function resets the previous tracking
        $this->assertFalse($nodes[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Function declaration does NOT have next attribute (FunctionLike nodes are not processed)
        $this->assertFalse($nodes[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // $d = 4 has no next
        $this->assertFalse($nodes[2]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Inside function: $b = 2 connects to $c = 3
        $functionStmts = $nodes[1]->stmts;
        $this->assertTrue($functionStmts[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($functionStmts[1], $functionStmts[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // $c = 3 connects to $d = 4 (because traverser visits in depth-first order)
        $this->assertTrue($functionStmts[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($nodes[2], $functionStmts[1]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }

    public function test_it_skips_nop_statements(): void
    {
        $code = <<<'PHP'
            <?php

            $a = 1;
            $b = 2;
            // Comment that becomes a Nop
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        // There should be 3 nodes: $a = 1, $b = 2, Nop (comment)
        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(Nop::class, $nodes[2]);

        // $a = 1 connects to $b = 2
        $this->assertTrue($nodes[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($nodes[1], $nodes[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // $b = 2 has no next (Nop is skipped)
        $this->assertFalse($nodes[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Nop should not have next attribute
        $this->assertFalse($nodes[2]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }

    public function test_it_only_processes_statement_nodes(): void
    {
        $code = <<<'PHP'
            <?php

            if (true) {
                $a = 1;
                $b = 2;
            }

            $c = 3;
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        // If statement connects to first statement inside the block (due to depth-first traversal)
        $this->assertTrue($nodes[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($nodes[0]->stmts[0], $nodes[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Inside if block: $a = 1 connects to $b = 2
        $ifStmts = $nodes[0]->stmts;
        $this->assertTrue($ifStmts[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($ifStmts[1], $ifStmts[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // $b = 2 connects to $c = 3 (due to depth-first traversal)
        $this->assertTrue($ifStmts[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($nodes[1], $ifStmts[1]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // The condition expression node should not have next attribute
        $this->assertFalse($nodes[0]->cond->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }

    public function test_it_handles_class_methods_as_function_boundaries(): void
    {
        $code = <<<'PHP'
            <?php

            class Test {
                public function foo() {
                    $a = 1;
                    $b = 2;
                }

                public function bar() {
                    $c = 3;
                    $d = 4;
                }
            }
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        $classMethods = $nodes[0]->stmts;

        // Methods are FunctionLike nodes, so they don't have next attributes
        $this->assertFalse($classMethods[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertFalse($classMethods[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Inside first method
        $fooStmts = $classMethods[0]->stmts;
        $this->assertTrue($fooStmts[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($fooStmts[1], $fooStmts[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Inside second method
        $barStmts = $classMethods[1]->stmts;
        $this->assertTrue($barStmts[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($barStmts[1], $barStmts[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }

    public function test_it_handles_closures_as_function_boundaries(): void
    {
        $code = <<<'PHP'
            <?php

            $a = 1;

            $closure = function() {
                $b = 2;
                $c = 3;
            };

            $d = 4;
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        // $a = 1 connects to $closure assignment
        $this->assertTrue($nodes[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($nodes[1], $nodes[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // $closure assignment does NOT connect to $d = 4 because the closure resets previous
        $this->assertFalse($nodes[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // $d = 4 has no next
        $this->assertFalse($nodes[2]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Inside closure
        $closureExpr = $nodes[1]->expr->expr; // Get the closure from the assignment
        $closureStmts = $closureExpr->stmts;
        $this->assertTrue($closureStmts[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($closureStmts[1], $closureStmts[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Last statement in closure connects to $d = 4 (because traverser visits in depth-first order)
        $this->assertTrue($closureStmts[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($nodes[2], $closureStmts[1]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }

    public function test_it_handles_empty_function_body(): void
    {
        $code = <<<'PHP'
            <?php

            $a = 1;

            function empty_function() {
            }

            $b = 2;
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        // $a = 1 has no next because function resets the previous tracking
        $this->assertFalse($nodes[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Function declaration does NOT have next attribute (FunctionLike nodes are not processed)
        $this->assertFalse($nodes[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // $b = 2 has no next
        $this->assertFalse($nodes[2]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }

    public function test_it_connects_return_statements_to_next_statements(): void
    {
        $code = <<<'PHP'
            <?php

            function hasMultipleReturns($condition) {
                if ($condition) {
                    return 'early';
                    $unreachable = true;
                }

                $a = 1;
                return 'normal';
                $afterReturn = 2;
            }
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        $functionStmts = $nodes[0]->stmts;
        $ifStmts = $functionStmts[0]->stmts;

        // Early return connects to unreachable statement
        $this->assertTrue($ifStmts[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($ifStmts[1], $ifStmts[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Normal return connects to statement after it
        $this->assertTrue($functionStmts[2]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($functionStmts[3], $functionStmts[2]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }

    public function test_it_does_not_connect_last_return_statement(): void
    {
        $code = <<<'PHP'
            <?php

            function lastReturn() {
                $a = 1;
                return $a;
            }
            PHP;

        $nodes = self::parseCode($code);

        $this->traverse($nodes, [new NextConnectingVisitor()]);

        $functionStmts = $nodes[0]->stmts;

        // $a = 1 connects to return
        $this->assertTrue($functionStmts[0]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
        $this->assertSame($functionStmts[1], $functionStmts[0]->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));

        // Last return has no next
        $this->assertFalse($functionStmts[1]->hasAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE));
    }
}
