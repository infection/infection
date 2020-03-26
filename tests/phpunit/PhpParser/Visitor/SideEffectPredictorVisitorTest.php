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

use Infection\PhpParser\Visitor\SideEffectPredictorVisitor;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class SideEffectPredictorVisitorTest extends TestCase
{
    public function test_it_returns_null_when_entering_node(): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $nodeMock = $this->createNotExpectingAnythingNodeMock();

        $this->assertNull($visitor->enterNode($nodeMock));
    }

    public function test_it_returns_null_when_leaving_node(): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $nodeMock = $this->createNotExpectingAnythingNodeMock();

        $this->assertNull($visitor->leaveNode($nodeMock));
    }

    public function test_it_does_not_update_attribute_for_non_expressions(): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $nodeMock = $this->createNotExpectingAnythingNodeMock();

        $visitor->leaveNode($nodeMock);
    }

    public function test_it_updates_attribute_for_expression(): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $expressionMock = $this->createExpressionMock(false);

        $visitor->leaveNode($expressionMock);
    }

    public function test_it_updates_attribute_with_default_value(): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $expressionMock = $this->createNotExpectingAnythingNodeMock(Node\Stmt\Expression::class);
        $visitor->enterNode($expressionMock);

        $expressionMock = $this->createExpressionMock(false);
        $visitor->leaveNode($expressionMock);
    }

    public function test_it_updates_attribute_to_true_after_seeing_method_call(): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $expressionMock = $this->createNotExpectingAnythingNodeMock(Node\Stmt\Expression::class);
        $visitor->enterNode($expressionMock);

        $methodCallMock = $this->createNotExpectingAnythingNodeMock(Node\Expr\MethodCall::class);
        $visitor->enterNode($methodCallMock);

        $expressionMock = $this->createExpressionMock(true);
        $visitor->leaveNode($expressionMock);
    }

    public function test_it_updates_attribute_to_true_after_seeing_unnamed_function_call(): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $expressionMock = $this->createNotExpectingAnythingNodeMock(Node\Stmt\Expression::class);
        $visitor->enterNode($expressionMock);

        $functionCallMock = $this->createNotExpectingAnythingNodeMock(Node\Expr\FuncCall::class);
        $functionCallMock->name = null;
        $visitor->enterNode($functionCallMock);

        $expressionMock = $this->createExpressionMock(true);
        $visitor->leaveNode($expressionMock);
    }

    /**
     * @dataProvider provideRestrictedNodeClassNames
     */
    public function test_it_updates_attribute_to_false_after_seeing_restricted_node(string $nodeClassName): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $expressionMock = $this->createNotExpectingAnythingNodeMock(Node\Stmt\Expression::class);
        $visitor->enterNode($expressionMock);

        $methodCallMock = $this->createNotExpectingAnythingNodeMock(Node\Expr\MethodCall::class);
        $visitor->enterNode($methodCallMock);

        $methodCallMock = $this->createNotExpectingAnythingNodeMock($nodeClassName);
        $visitor->enterNode($methodCallMock);

        $methodCallMock = $this->createNotExpectingAnythingNodeMock();
        $visitor->enterNode($methodCallMock);

        $expressionMock = $this->createExpressionMock(false);
        $visitor->leaveNode($expressionMock);
    }

    /**
     * @dataProvider provideRestrictedNodeClassNames
     */
    public function test_it_keeps_attribute_at_true_after_seeing_restricted_node_on_level_above(string $nodeClassName): void
    {
        $visitor = new SideEffectPredictorVisitor();

        $expressionMock = $this->createNotExpectingAnythingNodeMock(Node\Stmt\Expression::class);
        $visitor->enterNode($expressionMock);

        $methodCallMock = $this->createNotExpectingAnythingNodeMock(Node\Expr\MethodCall::class);
        $visitor->enterNode($methodCallMock);

        for ($i = 1; $i < 4; ++$i) {
            $expressionMock = $this->createNotExpectingAnythingNodeMock(Node\Stmt\Expression::class);
            $visitor->enterNode($expressionMock);

            $methodCallMock = $this->createNotExpectingAnythingNodeMock(Node\Expr\MethodCall::class);
            $visitor->enterNode($methodCallMock);

            $methodCallMock = $this->createNotExpectingAnythingNodeMock($nodeClassName);
            $visitor->enterNode($methodCallMock);

            $expressionMock = $this->createExpressionMock(false);
            $visitor->leaveNode($expressionMock);
        }

        $expressionMock = $this->createExpressionMock(true);
        $visitor->leaveNode($expressionMock);
    }

    public static function provideRestrictedNodeClassNames(): iterable
    {
        yield [Node\Expr\FuncCall::class];

        yield [Node\Expr\StaticCall::class];

        yield [Node\Expr\New_::class];
    }

    private function createNotExpectingAnythingNodeMock(string $originalClassName = Node::class)
    {
        $nodeMock = $this->createMock($originalClassName);
        $nodeMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        if ($nodeMock instanceof Node\Expr\FuncCall) {
            $nodeMock->name = new Node\Name('example');
        }

        return $nodeMock;
    }

    private function createExpressionMock(bool $attributeValue): Node\Stmt\Expression
    {
        $expressionMock = $this->createMock(Node\Stmt\Expression::class);
        $expressionMock
            ->expects($this->once())
            ->method('setAttribute')
            ->with(
                SideEffectPredictorVisitor::HAS_NODES_WITH_SIDE_EFFECTS_KEY,
                $attributeValue
            )
        ;

        return $expressionMock;
    }
}
