<?php

declare(strict_types=1);

namespace Infection\Tests\PhpParser\Ast\Visitor\AddIdToTraversedNodesVisitor;

final class Sequence
{
    /**
     * @var positive-int|0
     */
    private int $value = 0;

    /**
     * @return positive-int|0
     */
    public function next(): int
    {
        $value = $this->value;

        $this->value++;

        return $value;
    }
}
