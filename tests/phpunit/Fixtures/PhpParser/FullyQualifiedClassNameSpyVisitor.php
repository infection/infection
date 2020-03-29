<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class FullyQualifiedClassNameSpyVisitor extends NodeVisitorAbstract
{
    public $nodes = [];

    public function enterNode(Node $node): void
    {
        if (FullyQualifiedClassNameManipulator::hasFqcn($node)) {
            $this->nodes[] = $node;
        }
    }

    /**
     * @return Node[]
     */
    public function getCollectedNodes(): array
    {
        return $this->nodes;
    }
}
