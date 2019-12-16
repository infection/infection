<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use LogicException;
use PhpParser\Node;
use PhpParser\NodeVisitor;

final class FakeVisitor implements NodeVisitor
{
    public function beforeTraverse(array $nodes)
    {
        throw new LogicException();
    }

    public function enterNode(Node $node)
    {
        throw new LogicException();
    }

    public function leaveNode(Node $node)
    {
        throw new LogicException();
    }

    public function afterTraverse(array $nodes)
    {
        throw new LogicException();
    }
}
