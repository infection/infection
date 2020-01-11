<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\Visitor\IgnoreNode\IgnoresNode;
use PhpParser\Node;

final class FakeIgnores implements IgnoresNode
{
    public function ignores(Node $node): bool
    {
       return false;
    }
}
