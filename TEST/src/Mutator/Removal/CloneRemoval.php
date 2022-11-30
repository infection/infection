<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Removal;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class CloneRemoval implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes the clone keyword, e.g. replacing `clone $x` with `$x`.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $a = clone $x;
+ $a = $x;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield $node->expr);
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\Clone_;
    }
}
