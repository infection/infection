<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\Visitor\IgnoreNode\NodeIgnorer;
use PhpParser\Node;

final class FakeIgnorer implements NodeIgnorer
{
    public function ignores(Node $node): bool
    {
       return false;
    }
}
