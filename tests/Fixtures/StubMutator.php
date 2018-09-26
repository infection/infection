<?php
declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutator;
use PhpParser\Node;

class StubMutator extends Mutator
{
    public function mutate(Node $node): \Generator
    {
        yield [];
    }

    public function mutatesNode(Node $node): bool
    {
        return false;
    }
}
