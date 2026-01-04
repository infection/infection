<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\Tests\UnsupportedMethod;
use PhpParser\Node;
use PhpParser\NodeVisitor;

final class FakeVisitor implements NodeVisitor
{
    public function beforeTraverse(array $nodes): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function enterNode(Node $node): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function leaveNode(Node $node): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function afterTraverse(array $nodes): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
