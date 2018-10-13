<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Visitor;

use Infection\Visitor\CodeCoverageClassIgnoreVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class CodeCoverageClassIgnoreVisitorTest extends AbstractBaseVisitorTest
{
    private $spyVisitor;

    protected function setUp(): void
    {
        $this->spyVisitor = $this->getSpyVisitor();
    }

    public function test_it_do_not_travers_when_coverage_is_ignored(): void
    {
        $code = $this->getFileContent('Coverage/code-coverage-class-ignore.php');

        $this->parseAndTraverse($code);

        $this->assertFalse($this->spyVisitor->isNodeVisited(), 'ClassMethod node has been visited');
    }

    public function test_it_travers_nodes_when_coverage_is_not_ignored(): void
    {
        $code = $this->getFileContent('Coverage/code-coverage-class-not-ignored.php');

        $this->parseAndTraverse($code);

        $this->assertTrue($this->spyVisitor->isNodeVisited(), 'ClassMethod node has not been visited');
    }

    protected function parseAndTraverse(string $code): void
    {
        $nodes = $this->getNodes($code);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new CodeCoverageClassIgnoreVisitor());
        $traverser->addVisitor($this->spyVisitor);

        $traverser->traverse($nodes);
    }

    private function getSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            private $nodeVisited = false;

            public function leaveNode(Node $node): void
            {
                if ($node instanceof Node\Stmt\ClassMethod) {
                    $this->nodeVisited = true;
                }
            }

            public function isNodeVisited(): bool
            {
                return $this->nodeVisited;
            }
        };
    }
}
