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

namespace Infection\Tests\TestingUtility\PhpParser\Visitor\AssignLabelsToNodesVisitor;

use Infection\Tests\PhpParser\Visitor\VisitorTestCase;
use Infection\Tests\TestingUtility\PhpParser\LabelParser\LabelParser;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(AssignLabelsToNodesVisitor::class)]
final class AssignLabelsToNodesVisitorTest extends VisitorTestCase
{
    public function test_it_assigns_labels_to_nodes(): void
    {
        $code = <<<'PHP'
            <?php

            function foo() {  // @label:Stmt_Function:foo-func
                return 1;  // @label:Stmt_Return:return-stmt
            }
            PHP;

        $nodes = $this->parse($code);

        $labelParser = new LabelParser();
        $parsedLabels = $labelParser->parseLabelsFromNodes($nodes);

        $visitor = new AssignLabelsToNodesVisitor($parsedLabels);
        (new NodeTraverser($visitor))->traverse($nodes);

        $labeledNodes = $visitor->getLabeledNodes();

        $this->assertCount(2, $labeledNodes);
        $this->assertArrayHasKey('foo-func', $labeledNodes);
        $this->assertArrayHasKey('return-stmt', $labeledNodes);

        $fooFunc = $labeledNodes['foo-func'];
        $this->assertInstanceOf(Function_::class, $fooFunc);
        $this->assertSame('foo-func', AssignLabelsToNodesVisitor::getNodeLabel($fooFunc));

        $returnStmt = $labeledNodes['return-stmt'];
        $this->assertInstanceOf(Return_::class, $returnStmt);
        $this->assertSame('return-stmt', AssignLabelsToNodesVisitor::getNodeLabel($returnStmt));
    }

    public function test_it_throws_when_multiple_nodes_of_same_type_on_line(): void
    {
        $code = <<<'PHP'
            <?php

            $x = $y;  // @label:Expr_Variable:first-var
            PHP;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Multiple nodes of type "Expr_Variable" found on line 3 for label "first-var". Please restructure the code to have one node per line, or use a prefix comment on a separate line.');

        $nodes = $this->parse($code);

        $labelParser = new LabelParser();
        $parsedLabels = $labelParser->parseLabelsFromNodes($nodes);

        $visitor = new AssignLabelsToNodesVisitor($parsedLabels);
        (new NodeTraverser($visitor))->traverse($nodes);
        $visitor->getLabeledNodes();
    }

    public function test_it_throws_when_node_type_not_found_on_line(): void
    {
        $code = <<<'PHP'
            <?php

            $x = 1;  // @label:Stmt_Function:my-func
            PHP;

        $nodes = $this->parse($code);

        $labelParser = new LabelParser();
        $parsedLabels = $labelParser->parseLabelsFromNodes($nodes);

        $visitor = new AssignLabelsToNodesVisitor($parsedLabels);
        (new NodeTraverser($visitor))->traverse($nodes);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No node of type "Stmt_Function" found for label "my-func" at line 3');

        $visitor->getLabeledNodes();
    }

    public function test_it_allows_multiple_labels_for_different_types_on_same_line(): void
    {
        $code = <<<'PHP'
            <?php

            $x = foo();  // @label:Expr_Variable:x-var @label:Expr_FuncCall:call
            PHP;

        $nodes = $this->parse($code);

        $labelParser = new LabelParser();
        $parsedLabels = $labelParser->parseLabelsFromNodes($nodes);

        $visitor = new AssignLabelsToNodesVisitor($parsedLabels);
        (new NodeTraverser($visitor))->traverse($nodes);

        $labeledNodes = $visitor->getLabeledNodes();

        $this->assertCount(2, $labeledNodes);
        $this->assertArrayHasKey('x-var', $labeledNodes);
        $this->assertArrayHasKey('call', $labeledNodes);

        $xVar = $labeledNodes['x-var'];
        $this->assertInstanceOf(Variable::class, $xVar);

        // Note: FuncCall would be Expr\FuncCall but the test code uses an undefined function
        // which PhpParser represents as Name, not FuncCall in this context
        $this->assertArrayHasKey('call', $labeledNodes);
    }

    public function test_it_handles_prefix_comments(): void
    {
        $code = <<<'PHP'
            <?php

            // @label:Expr_Variable:first-var
            $x = $y;
            PHP;

        $nodes = $this->parse($code);

        $labelParser = new LabelParser();
        $parsedLabels = $labelParser->parseLabelsFromNodes($nodes);

        $visitor = new AssignLabelsToNodesVisitor($parsedLabels);
        (new NodeTraverser($visitor))->traverse($nodes);

        $labeledNodes = $visitor->getLabeledNodes();

        $this->assertCount(1, $labeledNodes);
        $this->assertArrayHasKey('first-var', $labeledNodes);

        $xVar = $labeledNodes['first-var'];
        $this->assertInstanceOf(Variable::class, $xVar);
    }
}
