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

namespace Infection\Tests\TestingUtility\PhpParser\LabelParser;

use Infection\Tests\PhpParser\Visitor\VisitorTestCase;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Function_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

#[CoversClass(LabelParser::class)]
#[CoversClass(ParsedLabels::class)]
#[CoversClass(NodeTypeConverter::class)]
final class LabelParserTest extends VisitorTestCase
{
    public function test_it_parses_suffix_comments(): void
    {
        $code = <<<'PHP'
            <?php

            function foo() {  // @label:Stmt_Function:foo-func
                $x = 1;  // @label:Expr_Variable:x-var
            }
            PHP;

        $nodes = $this->parse($code);
        $parser = new LabelParser();
        $result = $parser->parseLabelsFromNodes($nodes);

        $this->assertFalse($result->isEmpty());

        // Function is on line 3
        $labelsLine3 = $result->getLabelsForLine(3);
        $this->assertNotNull($labelsLine3);
        $this->assertArrayHasKey(Function_::class, $labelsLine3);
        $this->assertSame('foo-func', $labelsLine3[Function_::class]);

        // Variable is on line 4
        $labelsLine4 = $result->getLabelsForLine(4);
        $this->assertNotNull($labelsLine4);
        $this->assertArrayHasKey(Variable::class, $labelsLine4);
        $this->assertSame('x-var', $labelsLine4[Variable::class]);
    }

    public function test_it_parses_prefix_comments(): void
    {
        $code = <<<'PHP'
            <?php

            // @label:Stmt_Function:bar-func
            function bar() {
                // @label:Expr_Variable:y-var
                $y = 2;
            }
            PHP;

        $nodes = $this->parse($code);
        $parser = new LabelParser();
        $result = $parser->parseLabelsFromNodes($nodes);

        $this->assertFalse($result->isEmpty());

        // Function is on line 4, but label is on line 3 (prefix)
        $labelsLine4 = $result->getLabelsForLine(4);
        $this->assertNotNull($labelsLine4);
        $this->assertArrayHasKey(Function_::class, $labelsLine4);
        $this->assertSame('bar-func', $labelsLine4[Function_::class]);

        // Variable is on line 6, but label is on line 5 (prefix)
        $labelsLine6 = $result->getLabelsForLine(6);
        $this->assertNotNull($labelsLine6);
        $this->assertArrayHasKey(Variable::class, $labelsLine6);
        $this->assertSame('y-var', $labelsLine6[Variable::class]);
    }

    public function test_it_parses_block_comments(): void
    {
        $code = <<<'PHP'
            <?php

            /* @label:Stmt_Function:baz-func */
            function baz() {}
            PHP;

        $nodes = $this->parse($code);
        $parser = new LabelParser();
        $result = $parser->parseLabelsFromNodes($nodes);

        $this->assertFalse($result->isEmpty());

        $labelsLine4 = $result->getLabelsForLine(4);
        $this->assertNotNull($labelsLine4);
        $this->assertArrayHasKey(Function_::class, $labelsLine4);
        $this->assertSame('baz-func', $labelsLine4[Function_::class]);
    }

    public function test_it_returns_empty_for_no_labels(): void
    {
        $code = <<<'PHP'
            <?php

            function foo() {
                $x = 1;
            }
            PHP;

        $nodes = $this->parse($code);
        $parser = new LabelParser();
        $result = $parser->parseLabelsFromNodes($nodes);

        $this->assertTrue($result->isEmpty());
    }

    public function test_it_allows_hyphens_and_underscores_in_labels(): void
    {
        $code = <<<'PHP'
            <?php

            function foo() {  // @label:Stmt_Function:foo-func_123
                return 1;
            }
            PHP;

        $nodes = $this->parse($code);
        $parser = new LabelParser();
        $result = $parser->parseLabelsFromNodes($nodes);

        $labelsLine3 = $result->getLabelsForLine(3);
        $this->assertNotNull($labelsLine3);
        $this->assertSame('foo-func_123', $labelsLine3[Function_::class]);
    }

    #[DataProvider('invalidLabelNameProvider')]
    public function test_it_rejects_invalid_label_names(
        string $code,
        string $expectedMessage,
    ): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        $nodes = $this->parse($code);
        $parser = new LabelParser();
        $parser->parseLabelsFromNodes($nodes);
    }

    public static function invalidLabelNameProvider(): iterable
    {
        yield 'starts with number' => [
            <<<'PHP'
                <?php
                function foo() {  // @label:Stmt_Function:123invalid
                }
                PHP,
            'Invalid label name "123invalid" at line 2. Labels must match /^[a-zA-Z][a-zA-Z0-9_-]*$/',
        ];

        yield 'contains special characters' => [
            <<<'PHP'
                <?php
                function foo() {  // @label:Stmt_Function:invalid@label
                }
                PHP,
            'Invalid label name "invalid@label" at line 2. Labels must match /^[a-zA-Z][a-zA-Z0-9_-]*$/',
        ];
    }

    public function test_it_throws_on_duplicate_labels(): void
    {
        $code = <<<'PHP'
            <?php

            function foo() {  // @label:Stmt_Function:my-label
            }

            function bar() {  // @label:Stmt_Function:my-label
            }
            PHP;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Duplicate label "my-label" found at lines 3 and 6. Each label must be unique within a traversal.');

        $nodes = $this->parse($code);
        $parser = new LabelParser();
        $parser->parseLabelsFromNodes($nodes);
    }

    public function test_it_throws_on_invalid_node_type(): void
    {
        $code = <<<'PHP'
            <?php

            function foo() {  // @label:Invalid_NodeType:my-label
            }
            PHP;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid node type "Invalid_NodeType" for label "my-label" at line 3. Expected a valid PhpParser node type (e.g., Expr_Variable, Stmt_Function).');

        $nodes = $this->parse($code);
        $parser = new LabelParser();
        $parser->parseLabelsFromNodes($nodes);
    }
}
