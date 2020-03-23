<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\UnsupportedMethod;
use LogicException;
use PhpParser\Node;
use PhpParser\NodeVisitor;

final class FakeVisitor implements NodeVisitor
{
    public function beforeTraverse(array $nodes)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function enterNode(Node $node)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function leaveNode(Node $node)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function afterTraverse(array $nodes)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }
}
