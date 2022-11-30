<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use const PHP_VERSION_ID;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class NullSafePropertyCall implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces the nullsafe property call operator (`?->`) with (`->`).
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $object?->property;
+ $object->property;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\PropertyFetch($node->var, $node->name, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return PHP_VERSION_ID >= 80000 && $node instanceof Node\Expr\NullsafePropertyFetch;
    }
}
