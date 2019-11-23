<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

final class Fake extends Mutator
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function mutate(Node $node)
    {
        throw new \LogicException('Not expected to be called');
    }

    /**
     * {@inheritDoc}
     */
    protected function mutatesNode(Node $node): bool
    {
        throw new \LogicException('Not expected to be called');
    }
}
