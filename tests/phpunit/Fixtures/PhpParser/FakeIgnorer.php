<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use LogicException;
use PhpParser\Node;

final class FakeIgnorer implements NodeIgnorer
{
    public function ignores(Node $node): bool
    {
       throw new LogicException();
    }
}
