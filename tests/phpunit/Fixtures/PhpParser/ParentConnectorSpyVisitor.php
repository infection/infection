<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\PhpParser\Metadata\NodeAnnotator;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class ParentConnectorSpyVisitor extends NodeVisitorAbstract
{
    private ?array $nodes = null;

    public function beforeTraverse(array $nodes): void
    {
        $this->nodes = [];
    }

    public function enterNode(Node $node): void
    {
        $this->nodes[] = NodeAnnotator::findParent($node);
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
