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

use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use function array_map;
use Infection\Testing\SingletonContainer;
use Infection\Tests\TestingUtility\PhpParser\NodeDumper\NodeDumper;
use Infection\Tests\TestingUtility\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\Tests\TestingUtility\PhpParser\Visitor\KeepOnlyDesiredAttributesVisitor\KeepOnlyDesiredAttributesVisitor;
use Infection\Tests\TestingUtility\PhpParser\Visitor\RemoveUndesiredAttributesVisitor\RemoveUndesiredAttributesVisitor;
use function is_array;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use function sprintf;

abstract class VisitorTestCase extends TestCase
{
    protected Parser $parser;

    protected NodeDumper $dumper;

    protected function setUp(): void
    {
        $this->parser = $this->createParser();
        $this->dumper = $this->createDumper();
    }

    protected function createParser(): Parser
    {
        return SingletonContainer::getContainer()->getParser();
    }

    protected function createDumper(): NodeDumper
    {
        return new NodeDumper();
    }

    /**
     * @param Node[]|Node $nodeOrNodes
     */
    final protected function addIdsToNodes(array|Node $nodeOrNodes): void
    {
        $nodes = (array) $nodeOrNodes;

        $nodeTraverser = new NodeTraverser(
            new AddIdToTraversedNodesVisitor(),
        );
        $nodeTraverser->traverse($nodes);
    }

    /**
     * @param Node[]|Node $nodeOrNodes
     */
    final protected function removeUndesiredAttributes(
        array|Node $nodeOrNodes,
        string ...$attributes,
    ): void {
        $nodes = (array) $nodeOrNodes;

        $this->assertNotContains(
            MarkTraversedNodesAsVisitedVisitor::VISITED_ATTRIBUTE,
            $attributes,
            sprintf(
                'The attribute "%s" is never printed hence should not be removed. To display all nodes adjust NodeDumper `::dump()` method call instead.',
                MarkTraversedNodesAsVisitedVisitor::VISITED_ATTRIBUTE,
            ),
        );

        $nodeTraverser = new NodeTraverser(
            new RemoveUndesiredAttributesVisitor(...$attributes),
        );
        $nodeTraverser->traverse($nodes);
    }

    /**
     * @param Node[]|Node $nodeOrNodes
     */
    final protected function keepOnlyDesiredAttributes(
        array|Node $nodeOrNodes,
        string ...$attributes,
    ): void {
        $nodes = (array) $nodeOrNodes;

        $nodeTraverser = new NodeTraverser(
            new KeepOnlyDesiredAttributesVisitor(
                MarkTraversedNodesAsVisitedVisitor::VISITED_ATTRIBUTE,
                ...$attributes,
            ),
        );
        $nodeTraverser->traverse($nodes);
    }

    /**
     * @param array<string, list<mixed>> $records
     * @return array<string, list<string|list<string>>>
     */
    final protected function dumpRecordNodes(array $records): array
    {
        return array_map(
            fn (array $record) => [
                $record[0],
                $this->dumpRecursively($record[1]),
            ],
            $records,
        );
    }

    /**
     * @return array<string, list<string|list<string>>>
     */
    private function dumpRecursively(mixed $potentialNodes): array|string
    {
        if (is_array($potentialNodes)) {
            return array_map(
                $this->dumpRecursively(...),
                $potentialNodes,
            );
        }

        $this->assertInstanceOf(Node::class, $potentialNodes);

        return $this->dumper->dump($potentialNodes);
    }
}
