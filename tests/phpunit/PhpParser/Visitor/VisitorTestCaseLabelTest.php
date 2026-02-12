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

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VisitorTestCase::class)]
final class VisitorTestCaseLabelTest extends VisitorTestCase
{
    public function test_labelNodes_returns_only_labeled_nodes(): void
    {
        $code = <<<'PHP'
            <?php

            function foo() {  // @label:Stmt_Function:foo-function
                $x = 1;
                return $x;  // @label:Stmt_Return:return-stmt
            }
            PHP;

        $nodes = $this->labelNodes($this->parse($code));

        $this->assertCount(2, $nodes);
        $this->assertArrayHasKey('foo-function', $nodes);
        $this->assertArrayHasKey('return-stmt', $nodes);

        $fooFunction = $nodes['foo-function'];
        $this->assertInstanceOf(Function_::class, $fooFunction);
        $this->assertSame('foo', $fooFunction->name->toString());

        $returnStmt = $nodes['return-stmt'];
        $this->assertInstanceOf(Return_::class, $returnStmt);
    }

    public function test_addIdsToNodes_returns_both_numeric_ids_and_labels(): void
    {
        $code = <<<'PHP'
            <?php

            function bar() {  // @label:Stmt_Function:bar-func
                return 42;
            }
            PHP;

        $nodes = $this->addIdsToNodes($this->parse($code));

        // Should contain numeric IDs
        $this->assertArrayHasKey(0, $nodes);

        // Should also contain label
        $this->assertArrayHasKey('bar-func', $nodes);

        // Labeled node should be accessible via both keys
        $barFunc = $nodes['bar-func'];
        $this->assertInstanceOf(Function_::class, $barFunc);
    }

    public function test_addIdsToNodes_without_labels_returns_only_numeric_ids(): void
    {
        $code = <<<'PHP'
            <?php

            function baz() {
                return 99;
            }
            PHP;

        $nodes = $this->addIdsToNodes($this->parse($code));

        // Should contain numeric IDs
        $this->assertArrayHasKey(0, $nodes);

        // Should not have any string keys
        foreach (array_keys($nodes) as $key) {
            $this->assertIsInt($key);
        }
    }

    public function test_complex_labeling_scenario(): void
    {
        $code = <<<'PHP'
            <?php

            class Foo {  // @label:Stmt_Class:foo-class
                public function bar($items) {  // @label:Stmt_ClassMethod:bar-method
                    // @label:Expr_Variable:items-param
                    return $items[0];
                }
            }
            PHP;

        $nodes = $this->labelNodes($this->parse($code));

        $this->assertCount(3, $nodes);

        $fooClass = $nodes['foo-class'];
        $this->assertInstanceOf(Class_::class, $fooClass);
        $this->assertSame('Foo', $fooClass->name->toString());

        $barMethod = $nodes['bar-method'];
        $this->assertInstanceOf(ClassMethod::class, $barMethod);
        $this->assertSame('bar', $barMethod->name->toString());

        $itemsParam = $nodes['items-param'];
        $this->assertInstanceOf(Variable::class, $itemsParam);
        $this->assertSame('items', $itemsParam->name);
    }

    public function test_labelNodes_with_multiple_labels_on_same_line_different_types(): void
    {
        $code = <<<'PHP'
            <?php

            $result = getValue();  // @label:Expr_Assign:assignment @label:Expr_FuncCall:call
            PHP;

        $nodes = $this->labelNodes($this->parse($code));

        $this->assertArrayHasKey('assignment', $nodes);
        $this->assertInstanceOf(Assign::class, $nodes['assignment']);

        $this->assertArrayHasKey('call', $nodes);
    }

    public function test_labelNodes_with_prefix_and_suffix_comments(): void
    {
        $code = <<<'PHP'
            <?php

            // @label:Stmt_Function:prefix-func
            function foo() {
                $x = 1;  // @label:Expr_Variable:suffix-var
            }
            PHP;

        $nodes = $this->labelNodes($this->parse($code));

        $this->assertCount(2, $nodes);
        $this->assertArrayHasKey('prefix-func', $nodes);
        $this->assertArrayHasKey('suffix-var', $nodes);

        $this->assertInstanceOf(Function_::class, $nodes['prefix-func']);
        $this->assertInstanceOf(Variable::class, $nodes['suffix-var']);
    }

    public function test_labelNodes_with_block_comments(): void
    {
        $code = <<<'PHP'
            <?php

            /* @label:Stmt_Function:block-func */
            function test() {}
            PHP;

        $nodes = $this->labelNodes($this->parse($code));

        $this->assertCount(1, $nodes);
        $this->assertArrayHasKey('block-func', $nodes);
        $this->assertInstanceOf(Function_::class, $nodes['block-func']);
    }

    public function test_labelNodes_allows_hyphens_and_underscores(): void
    {
        $code = <<<'PHP'
            <?php

            function foo() {  // @label:Stmt_Function:my-func_123
            }
            PHP;

        $nodes = $this->labelNodes($this->parse($code));

        $this->assertArrayHasKey('my-func_123', $nodes);
        $this->assertInstanceOf(Function_::class, $nodes['my-func_123']);
    }
}
