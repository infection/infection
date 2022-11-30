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
final class NullSafeMethodCall implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces the nullsafe method call operator (`?->`) with (`->`).
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $object?->getObject();
+ $object->getObject();
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\MethodCall($node->var, $node->name, $node->args, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return PHP_VERSION_ID >= 80000 && $node instanceof Node\Expr\NullsafeMethodCall;
    }
}
