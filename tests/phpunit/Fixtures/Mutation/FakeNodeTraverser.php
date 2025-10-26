<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Mutation;

use Infection\Tests\UnsupportedMethod;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;

final class FakeNodeTraverser implements NodeTraverserInterface
{
    public function addVisitor(NodeVisitor $visitor)
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function removeVisitor(NodeVisitor $visitor)
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function traverse(array $nodes): array
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
