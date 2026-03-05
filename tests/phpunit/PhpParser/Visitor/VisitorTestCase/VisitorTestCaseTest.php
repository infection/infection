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

namespace Infection\Tests\PhpParser\Visitor\VisitorTestCase;

use Infection\Tests\TestingUtility\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use PhpParser\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VisitorTestCase::class)]
final class VisitorTestCaseTest extends TestCase
{
    private ConcreteVisitorTestCase $testCase;

    protected function setUp(): void
    {
        $this->testCase = new ConcreteVisitorTestCase('test');
        $this->testCase->setUp();
    }

    public function test_parse_returns_non_null_nodes(): void
    {
        $code = <<<'PHP'
            <?php

            $a = 1;
            PHP;

        $nodes = $this->testCase->parseCode($code);

        $this->assertCount(1, $nodes);
    }

    public function test_add_ids_to_nodes_returns_map_with_node_ids(): void
    {
        $code = <<<'PHP'
            <?php

            $a = 1;
            $b = 2;
            PHP;

        $nodes = $this->testCase->parseCode($code);

        $nodesById = $this->testCase->addIdsToNodesPublic($nodes);

        $this->assertIsArray($nodesById);
        $this->assertNotEmpty($nodesById);

        foreach ($nodesById as $id => $node) {
            $this->assertIsInt($id);
            $this->assertGreaterThanOrEqual(0, $id);
            $this->assertInstanceOf(Node::class, $node);
            $this->assertSame($id, AddIdToTraversedNodesVisitor::getNodeId($node));
        }
    }

    public function test_keep_only_desired_attributes_filters_attributes(): void
    {
        $code = <<<'PHP'
            <?php

            $a = 1;
            PHP;

        $nodes = $this->testCase->parseCode($code);

        $firstNode = $nodes[0];

        $firstNode->setAttribute('custom1', 'value1');
        $firstNode->setAttribute('custom2', 'value2');
        $firstNode->setAttribute('custom3', 'value3');

        $this->testCase->keepOnlyDesiredAttributesPublic(
            [$firstNode],
            'custom1',
            'custom3',
        );

        $expected = [
            'custom1' => 'value1',
            'custom3' => 'value3',
        ];

        $actual = $firstNode->getAttributes();

        $this->assertEquals($expected, $actual);
    }
}
