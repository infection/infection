<?php
declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

class StubMutator extends Mutator
{
    public function mutate(Node $node): \Generator
    {
    }

    public function mutatesNode(Node $node): bool
    {
        return false;
    }
}
