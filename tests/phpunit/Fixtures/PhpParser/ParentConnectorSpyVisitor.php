<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\PhpParser\Visitor\ParentConnectorVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use function array_unshift;

final class ParentConnectorSpyVisitor extends NodeVisitorAbstract
{
    private $nodes;

    public function beforeTraverse(array $nodes): void
    {
        $this->nodes = [];
    }

    public function enterNode(Node $node): void
    {
        $this->nodes[] = ParentConnectorVisitor::findParent($node);
    }

    public function leaveNode(Node $node): void
    {
    }

    /**
     * @return array<Node|null>
     */
    public function getCollectedNodes(): array
    {
        return $this->nodes;
    }
}
